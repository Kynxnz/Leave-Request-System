<?php
namespace Database\Seeders;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $types=[
            ['name'=>'Vacation Leave','description'=>'Planned personal or vacation time.','color'=>'#2563eb','annual_allowance'=>15,'tracks_balance'=>true,'is_active'=>true],
            ['name'=>'Sick Leave','description'=>'Leave for illness or medical recovery.','color'=>'#dc2626','annual_allowance'=>10,'tracks_balance'=>true,'is_active'=>true],
            ['name'=>'Emergency Leave','description'=>'Urgent personal or family matters.','color'=>'#d97706','annual_allowance'=>5,'tracks_balance'=>true,'is_active'=>true],
            ['name'=>'Unpaid Leave','description'=>'Leave without balance deduction.','color'=>'#64748b','annual_allowance'=>0,'tracks_balance'=>false,'is_active'=>true],
        ];
        foreach($types as $type) LeaveType::updateOrCreate(['name'=>$type['name']],$type);
        $admin=User::updateOrCreate(['email'=>'admin@example.com'],['name'=>'System Administrator','password'=>Hash::make('password123'),'role'=>'Administrator','status'=>'active']);
        $manager=User::updateOrCreate(['email'=>'manager@example.com'],['name'=>'Maria Santos','password'=>Hash::make('password123'),'role'=>'Manager','status'=>'active']);
        $staff=User::updateOrCreate(['email'=>'staff@example.com'],['name'=>'Juan Cruz','password'=>Hash::make('password123'),'role'=>'Staff','manager_id'=>$manager->id,'status'=>'active']);
        $staff2=User::updateOrCreate(['email'=>'ana@example.com'],['name'=>'Ana Reyes','password'=>Hash::make('password123'),'role'=>'Staff','manager_id'=>$manager->id,'status'=>'active']);
        foreach([$staff,$staff2] as $u) foreach(LeaveType::where('tracks_balance',true)->get() as $type) $u->leaveBalances()->updateOrCreate(['leave_type_id'=>$type->id,'year'=>now()->year],['allocated'=>$type->annual_allowance,'used'=>0]);
    }
}
