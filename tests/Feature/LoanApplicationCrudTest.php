<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanApplicationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_create_a_loan_application(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $customer = $this->createCustomer();
        $user = $customer->user;

        $this->actingAs($user)
            ->post(route('customer.loans.store'), $this->loanData())
            ->assertRedirect(route('customer.loans.index'));

        $this->assertDatabaseHas('loan_applications', [
            'amount' => 1000000,
            'term_months' => 12,
            'interest_rate' => config('loan.interest_rate'),
            'purpose' => 'Business expansion',
            'status' => 'pending',
        ]);

        $loan = LoanApplication::first();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'message' => "A new loan application #{$loan->id} was submitted.",
        ]);
    }

    public function test_customer_can_view_their_loan_list_and_details(): void
    {
        $customer = $this->createCustomer();
        $user = $customer->user;
        $loan = $this->createLoan($customer);

        $this->actingAs($user)
            ->get(route('customer.loans.index'))
            ->assertOk()
            ->assertViewHas('loans', function ($loans) use ($loan) {
                return $loans->contains('id', $loan->id);
            });

        $this->actingAs($user)
            ->get(route('customer.loans.show', $loan))
            ->assertOk()
            ->assertSee('Loan Application #'.$loan->id)
            ->assertSee($loan->purpose);
    }

    public function test_customer_can_cancel_a_pending_loan_without_deleting_it(): void
    {
        $customer = $this->createCustomer();
        $user = $customer->user;
        $loan = $this->createLoan($customer);

        $this->actingAs($user)
            ->patch(route('customer.loans.cancel', $loan))
            ->assertRedirect(route('customer.loans.index'));

        $loan->refresh();

        $this->assertSame('cancelled', $loan->status);
        $this->assertNotNull($loan->cancelled_at);
        $this->assertDatabaseHas('loan_applications', ['id' => $loan->id]);
    }

    public function test_customer_cannot_view_another_customers_loan(): void
    {
        $firstCustomer = $this->createCustomer();
        $secondCustomer = $this->createCustomer();
        $loan = $this->createLoan($firstCustomer);

        $this->actingAs($secondCustomer->user)
            ->get(route('customer.loans.show', $loan))
            ->assertForbidden();
    }

    public function test_loan_detail_displays_the_repayment_summary(): void
    {
        $customer = $this->createCustomer();
        $loan = $this->createLoan($customer);

        $this->actingAs($customer->user)
            ->get(route('customer.loans.show', $loan))
            ->assertOk()
            ->assertSee('Total Interest')
            ->assertSee('100,000.00 MMK')
            ->assertSee('Total Payable')
            ->assertSee('1,100,000.00 MMK')
            ->assertSee('Monthly Installment')
            ->assertSee('91,666.67 MMK');
    }

    public function test_admin_can_filter_loans_by_status_customer_and_amount(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $firstCustomer = $this->createCustomer();
        $secondCustomer = $this->createCustomer();
        $firstLoan = $this->createLoan($firstCustomer);
        $secondLoan = $this->createLoan($secondCustomer);

        $firstLoan->update(['amount' => 500000]);
        $secondLoan->update([
            'amount' => 2000000,
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.loans.index', [
                'status' => 'pending',
                'customer_id' => $firstCustomer->id,
                'min_amount' => 100000,
                'max_amount' => 1000000,
            ]))
            ->assertOk()
            ->assertViewHas('loans', function ($loans) use ($firstLoan, $secondLoan) {
                return $loans->contains('id', $firstLoan->id)
                    && ! $loans->contains('id', $secondLoan->id);
            });
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

    private function createLoan(Customer $customer): LoanApplication
    {
        return $customer->loanApplications()->create([
            'amount' => 1000000,
            'term_months' => 12,
            'interest_rate' => config('loan.interest_rate'),
            'purpose' => 'Business expansion',
            'status' => 'pending',
            'application_date' => today(),
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
