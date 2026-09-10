<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\LoanRepayment;
use App\Models\User;
use App\Services\LoanApplicationService;
use App\Services\RepaymentScheduleService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_cannot_create_another_pending_loan(): void
    {
        $customer = $this->createCustomer();
        $user = $customer->user;
        $this->createLoan($customer);

        $this->actingAs($user)
            ->post(route('customer.loans.store'), $this->loanData())
            ->assertSessionHasErrors('loan');

        $this->assertDatabaseCount('loan_applications', 1);
    }

    public function test_amount_and_term_must_be_within_configured_ranges(): void
    {
        $customer = $this->createCustomer();
        $user = $customer->user;

        $invalidValues = [
            ['amount', config('loan.amount.min') - 1],
            ['amount', config('loan.amount.max') + 1],
            ['term_months', config('loan.term_months.min') - 1],
            ['term_months', config('loan.term_months.max') + 1],
        ];

        foreach ($invalidValues as [$field, $value]) {
            $this->actingAs($user)
                ->post(route('customer.loans.store'), array_merge($this->loanData(), [$field => $value]))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('loan_applications', 0);
    }

    public function test_minimum_and_maximum_amount_and_term_are_allowed(): void
    {
        $firstCustomer = $this->createCustomer();
        $secondCustomer = $this->createCustomer();
        $firstUser = $firstCustomer->user;
        $secondUser = $secondCustomer->user;

        $this->actingAs($firstUser)
            ->post(route('customer.loans.store'), array_merge($this->loanData(), [
                'amount' => config('loan.amount.min'),
                'term_months' => config('loan.term_months.min'),
            ]))
            ->assertSessionHasNoErrors();

        $this->actingAs($secondUser)
            ->post(route('customer.loans.store'), array_merge($this->loanData(), [
                'amount' => config('loan.amount.max'),
                'term_months' => config('loan.term_months.max'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('loan_applications', 2);
    }

    public function test_only_admin_can_approve_or_reject_loans(): void
    {
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');
        $loan = $this->createLoan($customer, $officer);

        $this->actingAs($officer)
            ->patch(route('admin.loans.approve', $loan), ['decision_notes' => 'Approved'])
            ->assertForbidden();

        $this->actingAs($officer)
            ->patch(route('admin.loans.reject', $loan), ['decision_notes' => 'Rejected'])
            ->assertForbidden();

        $this->assertSame('pending', $loan->fresh()->status);
    }

    public function test_approved_loan_cannot_be_approved_again_or_rejected(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');
        $loan = $this->createLoan($customer, $officer);

        $this->actingAs($admin)
            ->patch(route('admin.loans.approve', $loan), ['decision_notes' => 'Approved'])
            ->assertRedirect(route('admin.loans.show', $loan));

        $loan->refresh();

        $this->assertSame('approved', $loan->status);
        $this->assertNotNull($loan->approved_at);
        $this->assertDatabaseCount('loan_repayments', 12);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $loan->customer->user_id,
            'message' => "Your loan application #{$loan->id} was approved.",
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.loans.approve', $loan), ['decision_notes' => 'Approve again'])
            ->assertSessionHasErrors('loan');

        $this->actingAs($admin)
            ->patch(route('admin.loans.reject', $loan), ['decision_notes' => 'Reject again'])
            ->assertSessionHasErrors('loan');

        $this->assertSame('approved', $loan->fresh()->status);
        $this->assertDatabaseCount('loan_repayments', 12);
    }

    public function test_rejected_loan_cannot_be_approved(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $loan = $this->createLoan($customer);

        $this->actingAs($admin)
            ->patch(route('admin.loans.reject', $loan), ['decision_notes' => 'Not eligible'])
            ->assertRedirect(route('admin.loans.show', $loan));

        $this->actingAs($admin)
            ->patch(route('admin.loans.approve', $loan), ['decision_notes' => 'Approve again'])
            ->assertSessionHasErrors('loan');

        $loan->refresh();

        $this->assertSame('rejected', $loan->status);
        $this->assertNull($loan->approved_at);
        $this->assertDatabaseCount('loan_repayments', 0);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $loan->customer->user_id,
            'message' => "Your loan application #{$loan->id} was rejected.",
        ]);
    }

    public function test_approval_is_rolled_back_when_repayment_creation_fails(): void
    {
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');
        $loan = $this->createLoan($customer, $officer);

        $repaymentService = $this->createMock(RepaymentScheduleService::class);
        $repaymentService->expects($this->once())
            ->method('createFor')
            ->willThrowException(new \RuntimeException('Repayment creation failed.'));

        $loanService = new LoanApplicationService($repaymentService);

        try {
            $loanService->approve($loan, 'Approved');
            $this->fail('The repayment exception was not thrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Repayment creation failed.', $exception->getMessage());
        }

        $loan->refresh();

        $this->assertSame('pending', $loan->status);
        $this->assertNull($loan->approved_at);
        $this->assertDatabaseCount('loan_repayments', 0);
    }

    public function test_loan_must_have_an_officer_before_approval(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $loan = $this->createLoan($customer);

        $this->actingAs($admin)
            ->patch(route('admin.loans.approve', $loan), [
                'decision_notes' => 'Approved',
            ])
            ->assertSessionHasErrors('loan');

        $this->assertSame('pending', $loan->fresh()->status);
        $this->assertDatabaseCount('loan_repayments', 0);
    }

    public function test_admin_can_assign_a_loan_officer(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');
        $loan = $this->createLoan($customer);

        $this->actingAs($admin)
            ->patch(route('admin.loans.assign', $loan), [
                'assigned_reviewer_id' => $officer->id,
            ])
            ->assertRedirect(route('admin.loans.show', $loan));

        $this->assertSame($officer->id, $loan->fresh()->assigned_reviewer_id);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $officer->id,
            'message' => "Loan application #{$loan->id} was assigned to you.",
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->user_id,
            'message' => "A loan officer was assigned to your loan application #{$loan->id}.",
        ]);
    }

    public function test_rejection_requires_decision_notes(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $loan = $this->createLoan($customer);

        $this->actingAs($admin)
            ->patch(route('admin.loans.reject', $loan), [])
            ->assertSessionHasErrors('decision_notes');

        $this->assertSame('pending', $loan->fresh()->status);
    }

    public function test_roles_cannot_access_another_roles_pages(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');

        $this->actingAs($customer->user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($officer)
            ->get(route('customer.dashboard'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('loan-officer.dashboard'))
            ->assertForbidden();
    }

    public function test_loan_officer_can_only_view_assigned_loans(): void
    {
        $customer = $this->createCustomer();
        $assignedOfficer = User::factory()->create();
        $assignedOfficer->assignRole('loan_officer');
        $otherOfficer = User::factory()->create();
        $otherOfficer->assignRole('loan_officer');
        $loan = $this->createLoan($customer, $assignedOfficer);

        $this->actingAs($assignedOfficer)
            ->get(route('loan-officer.loans.show', $loan))
            ->assertOk();

        $this->actingAs($otherOfficer)
            ->get(route('loan-officer.loans.show', $loan))
            ->assertForbidden();
    }

    public function test_customer_submits_payment_and_officer_confirms_it(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');
        $loan = $this->createLoan($customer, $officer);
        $loan->update(['status' => 'disbursed']);
        $repayment = $this->createRepayment($loan);
        $screenshot = UploadedFile::fake()->create('payment.jpg', 100, 'image/jpeg');

        $this->actingAs($customer->user)
            ->post(route('customer.repayments.store', [$loan, $repayment]), [
                'amount' => 110000,
                'payment_screenshot' => $screenshot,
            ])
            ->assertRedirect(route('customer.loans.show', $loan));

        $repayment->refresh();

        $this->assertSame('submitted', $repayment->status);
        Storage::disk('local')->assertExists($repayment->payment_screenshot);

        $this->actingAs($customer->user)
            ->get(route('repayments.proof', $repayment))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('repayments.proof', $repayment))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('repayments.proof', $repayment))
            ->assertOk();

        $otherCustomer = $this->createCustomer();

        $this->actingAs($otherCustomer->user)
            ->get(route('repayments.proof', $repayment))
            ->assertForbidden();

        $this->actingAs($officer)
            ->patch(route('loan-officer.repayments.paid', [$loan, $repayment]))
            ->assertRedirect(route('loan-officer.loans.show', $loan));

        $this->assertSame('paid', $repayment->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->user_id,
            'message' => "Payment for installment #{$repayment->installment_number} of loan #{$loan->id} was confirmed.",
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.loans.close', $loan))
            ->assertRedirect(route('admin.loans.show', $loan));

        $this->assertSame('closed', $loan->fresh()->status);
    }

    public function test_loan_officer_cannot_confirm_a_pending_repayment(): void
    {
        $customer = $this->createCustomer();
        $officer = User::factory()->create();
        $officer->assignRole('loan_officer');
        $loan = $this->createLoan($customer, $officer);
        $loan->update(['status' => 'disbursed']);
        $repayment = $this->createRepayment($loan);

        $this->actingAs($officer)
            ->patch(route('loan-officer.repayments.paid', [$loan, $repayment]))
            ->assertSessionHasErrors('repayment');

        $this->assertSame('pending', $repayment->fresh()->status);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    private function createCustomer(): Customer
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user->customer()->create([
            'phone' => '09123456789',
            'address' => 'Yangon',
        ]);
    }

    private function createLoan(Customer $customer, ?User $officer = null): LoanApplication
    {
        return $customer->loanApplications()->create([
            'assigned_reviewer_id' => $officer ? $officer->id : null,
            'amount' => 1200000,
            'term_months' => 12,
            'interest_rate' => config('loan.interest_rate'),
            'purpose' => 'Business expansion',
            'status' => 'pending',
            'application_date' => today(),
        ]);
    }

    private function createRepayment(LoanApplication $loan): LoanRepayment
    {
        return $loan->repayments()->create([
            'installment_number' => 1,
            'due_date' => today()->addMonth(),
            'amount' => 110000,
            'status' => 'pending',
        ]);
    }

    private function loanData(): array
    {
        return [
            'amount' => 1000000,
            'term_months' => 12,
            'purpose' => 'Business expansion',
            'supporting_notes' => 'Purchase new equipment',
        ];
    }
}
