<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Schedule;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ScheduleExport;

class ShiftController extends Controller
{
    public function index()
    {
        $people = Person::all();
        $schedules = Schedule::with('person')
            ->orderBy('date')
            ->orderBy('shift_type')
            ->get();
            
        return view('shifts.index', compact('people', 'schedules'));
    }
    
    public function storePeople(Request $request)
    {
        $request->validate([
            'people' => 'required|array|size:4',
            'people.*' => 'required|string|max:255',
        ]);
        
        // Clear existing people and schedules
        Person::query()->delete();
        Schedule::query()->delete();
        
        // Create new people
        foreach ($request->people as $name) {
            Person::create(['name' => trim($name)]);
        }
        
        return redirect()->route('shifts.index')->with('success', 'People added successfully!');
    }
    
    public function generateSchedule(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'duration_weeks' => 'required|integer|min:1|max:52',
        ]);
        
        $people = Person::all();
        
        if ($people->count() !== 4) {
            return redirect()->back()->with('error', 'Please add exactly 4 people before generating schedule.');
        }
        
        // Clear existing schedules
        Schedule::query()->delete();
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addWeeks($request->duration_weeks);
        
        // Initialize rotation: person index 0 starts with Group 1 (on duty first 4 days)
        $peopleArray = $people->toArray();
        
        // Generate schedule for each day
        $currentDate = $startDate->copy();
        $dayCounter = 0;
        
        while ($currentDate->lte($endDate)) {
            $cycleDay = $dayCounter % 8; // 8-day cycle (4 on, 4 off)
            
            // Determine which people are on duty based on the cycle
            if ($cycleDay < 4) {
                // First 4 days: people 0,1 on duty, people 2,3 off duty
                $onDutyIndices = [0, 1];
                $offDutyIndices = [2, 3];
            } else {
                // Next 4 days: people 2,3 on duty, people 0,1 off duty
                $onDutyIndices = [2, 3];
                $offDutyIndices = [0, 1];
            }
            
            // Create schedules for on-duty people
            Schedule::create([
                'date' => $currentDate->toDateString(),
                'person_id' => $peopleArray[$onDutyIndices[0]]['id'],
                'shift_type' => 'A',
                'status' => 'on_duty'
            ]);
            
            Schedule::create([
                'date' => $currentDate->toDateString(),
                'person_id' => $peopleArray[$onDutyIndices[1]]['id'],
                'shift_type' => 'B',
                'status' => 'on_duty'
            ]);
            
            // Create schedules for off-duty people
            foreach ($offDutyIndices as $offIndex) {
                Schedule::create([
                    'date' => $currentDate->toDateString(),
                    'person_id' => $peopleArray[$offIndex]['id'],
                    'shift_type' => 'A', // Doesn't matter for off-duty
                    'status' => 'off_duty'
                ]);
            }
            
            $currentDate->addDay();
            $dayCounter++;
        }
        
        return redirect()->route('shifts.index')->with('success', 'Schedule generated successfully!');
    }
    
    public function export()
    {
        $schedules = Schedule::with('person')
            ->orderBy('date')
            ->orderBy('shift_type')
            ->get();
            
        if ($schedules->isEmpty()) {
            return redirect()->back()->with('error', 'No schedule data to export. Please generate a schedule first.');
        }
        
        return Excel::download(new ScheduleExport($schedules), 'shift_schedule.xlsx');
    }
}
