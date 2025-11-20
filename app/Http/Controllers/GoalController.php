<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalRequest;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    protected $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'active');
        
        $query = auth()->user()->goals();
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $goals = $query->orderBy('target_date', 'asc')->get();
        
        $goalsWithProgress = $goals->map(function ($goal) {
            $goal->progress = $this->goalService->calculateProgress($goal);
            $goal->estimated_completion = $this->goalService->getEstimatedCompletionDate($goal);
            return $goal;
        });

        return view('goals.index', compact('goalsWithProgress', 'status'));
    }

    public function create()
    {
        return view('goals.create');
    }

    public function store(GoalRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['current_amount'] = 0;
        $data['status'] = 'active';

        Goal::create($data);

        return redirect()->route('goals.index')->with('success', 'Goal created successfully.');
    }

    public function show(Goal $goal)
    {
        $this->authorize('view', $goal);
        
        $progress = $this->goalService->calculateProgress($goal);
        $estimatedCompletion = $this->goalService->getEstimatedCompletionDate($goal);
        
        return view('goals.show', compact('goal', 'progress', 'estimatedCompletion'));
    }

    public function edit(Goal $goal)
    {
        $this->authorize('update', $goal);
        return view('goals.edit', compact('goal'));
    }

    public function update(GoalRequest $request, Goal $goal)
    {
        $this->authorize('update', $goal);
        
        $data = $request->validated();
        
        // Check if goal should be marked as completed
        if ($data['current_amount'] >= $goal->target_amount && $goal->status !== 'completed') {
            $data['status'] = 'completed';
            
            // Send achievement notification
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendGoalAchievedNotification(auth()->id(), $goal->name);
        }
        
        $goal->update($data);

        return redirect()->route('goals.index')->with('success', 'Goal updated successfully.');
    }

    public function destroy(Goal $goal)
    {
        $this->authorize('delete', $goal);
        
        $goal->delete();

        return redirect()->route('goals.index')->with('success', 'Goal deleted successfully.');
    }

    public function updateProgress(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);
        
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $wasCompleted = $goal->isCompleted();
        $this->goalService->updateProgress($goal, $request->amount);
        $goal->refresh();

        // Send achievement notification if goal just completed
        if (!$wasCompleted && $goal->isCompleted()) {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendGoalAchievedNotification(auth()->id(), $goal->name);
        }

        return back()->with('success', 'Progress updated successfully.');
    }

    public function toggleStatus(Goal $goal)
    {
        $this->authorize('update', $goal);
        
        $newStatus = $goal->status === 'active' ? 'paused' : 'active';
        $goal->update(['status' => $newStatus]);

        return back()->with('success', 'Goal status updated.');
    }
}
