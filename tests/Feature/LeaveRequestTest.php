<?php
namespace Tests\Feature;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_registration_creates_staff_only(): void
    {
        LeaveType::create(['name'=>'Vacation','annual_allowance'=>15,'tracks_balance'=>true,'is_active'=>true]);
        $this->post(route('register.store'),['name'=>'New Staff','email'=>'new@example.com','password'=>'password123','password_confirmation'=>'password123'])->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users',['email'=>'new@example.com','role'=>'Staff']);
    }
    public function test_staff_can_submit_and_assigned_manager_can_approve(): void
    {
        $type=LeaveType::create(['name'=>'Vacation','annual_allowance'=>15,'tracks_balance'=>true,'is_active'=>true]);
        $manager=User::create(['name'=>'Manager','email'=>'manager@test.com','password'=>Hash::make('password123'),'role'=>'Manager','status'=>'active']);
        $staff=User::create(['name'=>'Staff','email'=>'staff@test.com','password'=>Hash::make('password123'),'role'=>'Staff','manager_id'=>$manager->id,'status'=>'active']);
        $staff->leaveBalances()->create(['leave_type_id'=>$type->id,'year'=>now()->year,'allocated'=>15,'used'=>0]);
        $this->actingAs($staff)->post(route('leave-requests.store'),['leave_type_id'=>$type->id,'start_date'=>now()->addWeek()->toDateString(),'end_date'=>now()->addDays(9)->toDateString(),'reason'=>'Personal leave'])->assertRedirect(route('leave-requests.index'));
        $request=$staff->leaveRequests()->first(); $this->assertSame('pending',$request->status); $this->assertSame($manager->id,$request->manager_id);
        $this->actingAs($manager)->post(route('approvals.approve',$request),['remarks'=>'Approved'])->assertRedirect();
        $this->assertDatabaseHas('leave_requests',['id'=>$request->id,'status'=>'approved']);
    }
    public function test_manager_cannot_approve_another_managers_request(): void
    {
        $type=LeaveType::create(['name'=>'Vacation','annual_allowance'=>15,'tracks_balance'=>true,'is_active'=>true]);
        $m1=User::create(['name'=>'M1','email'=>'m1@test.com','password'=>Hash::make('password123'),'role'=>'Manager','status'=>'active']);
        $m2=User::create(['name'=>'M2','email'=>'m2@test.com','password'=>Hash::make('password123'),'role'=>'Manager','status'=>'active']);
        $staff=User::create(['name'=>'Staff','email'=>'s@test.com','password'=>Hash::make('password123'),'role'=>'Staff','manager_id'=>$m1->id,'status'=>'active']);
        $staff->leaveBalances()->create(['leave_type_id'=>$type->id,'year'=>now()->year,'allocated'=>15,'used'=>0]);
        $this->actingAs($staff)->post(route('leave-requests.store'),['leave_type_id'=>$type->id,'start_date'=>now()->addWeek()->toDateString(),'end_date'=>now()->addWeek()->toDateString(),'reason'=>'Personal leave']);
        $request=$staff->leaveRequests()->first(); $this->actingAs($m2)->post(route('approvals.approve',$request))->assertForbidden();
    }
}
