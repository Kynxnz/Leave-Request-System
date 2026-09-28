<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(){return view('leave-types.index',['leaveTypes'=>LeaveType::latest()->paginate(15)]);}
    public function create(){return view('leave-types.create',['leaveType'=>new LeaveType(['annual_allowance'=>15,'color'=>'#2563eb','is_active'=>true,'tracks_balance'=>true])]);}
    public function store(Request $r){$d=$this->validated($r); LeaveType::create($d); return redirect()->route('leave-types.index')->with('success','Leave type created successfully.');}
    public function edit(LeaveType $leaveType){return view('leave-types.edit',compact('leaveType'));}
    public function update(Request $r,LeaveType $leaveType){$leaveType->update($this->validated($r,$leaveType)); return redirect()->route('leave-types.index')->with('success','Leave type updated successfully.');}
    public function destroy(LeaveType $leaveType){abort_if($leaveType->leaveRequests()->exists(),422,'This leave type has historical requests and cannot be deleted. Deactivate it instead.'); $leaveType->delete(); return back()->with('success','Leave type deleted.');}
    private function validated(Request $r,?LeaveType $type=null):array {return $r->validate(['name'=>['required','string','max:100','unique:leave_types,name'.($type?','.$type->id:'')],'description'=>['nullable','string','max:1000'],'color'=>['required','regex:/^#[0-9A-Fa-f]{6}$/'],'annual_allowance'=>['required','integer','min:0','max:365'],'tracks_balance'=>['boolean'],'is_active'=>['boolean']])+['tracks_balance'=>$r->boolean('tracks_balance'),'is_active'=>$r->boolean('is_active')];}
}
