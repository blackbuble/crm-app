@php
    $record = $getRecord();
    
    $activities = collect();

    // 1. Follow Ups
    if ($record->followUps) {
        foreach ($record->followUps as $followUp) {
            $activities->push([
                'type' => 'follow_up',
                'date' => $followUp->created_at,
                'icon' => 'heroicon-m-chat-bubble-left-right',
                'color' => 'success',
                'title' => 'Follow-up: ' . ucfirst($followUp->type),
                'description' => $followUp->notes,
                'user' => $followUp->user->name ?? 'Unknown',
                'meta' => $followUp->status,
            ]);
        }
    }

    // 2. Quotations
    if ($record->quotations) {
        foreach ($record->quotations as $quotation) {
            $activities->push([
                'type' => 'quotation',
                'date' => $quotation->created_at,
                'icon' => 'heroicon-m-document-text',
                'color' => 'warning',
                'title' => 'Quotation Generated',
                'description' => "Number: {$quotation->quotation_number} - Total: " . format_currency($quotation->total),
                'user' => $quotation->user->name ?? 'Unknown',
                'meta' => ucfirst($quotation->status),
            ]);
        }
    }

    // 3. Assignments
    if ($record->assignments) {
        foreach ($record->assignments as $assignment) {
            $from = $assignment->fromUser->name ?? 'System';
            $to = $assignment->toUser->name ?? 'Unknown';
            $activities->push([
                'type' => 'assignment',
                'date' => $assignment->created_at,
                'icon' => 'heroicon-m-arrow-path',
                'color' => 'primary',
                'title' => 'Customer Reassigned',
                'description' => "Transferred from {$from} to {$to}" . ($assignment->notes ? " - Note: {$assignment->notes}" : ""),
                'user' => $assignment->assignedBy->name ?? 'System',
                'meta' => 'Transfer',
            ]);
        }
    }

    // 4. Creation
    $activities->push([
        'type' => 'created',
        'date' => $record->created_at,
        'icon' => 'heroicon-m-star',
        'color' => 'gray',
        'title' => 'Customer Created',
        'description' => 'Customer record created in the system.',
        'user' => $record->assignedUser->name ?? 'System', 
        'meta' => 'New',
    ]);

    $sortedActivities = $activities->sortByDesc('date');
@endphp

<div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
    @foreach($sortedActivities as $activity)
        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
            
            <!-- Icon -->
            <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-50 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                <x-icon :name="$activity['icon']" class="w-5 h-5 text-{{ $activity['color'] }}-500" />
            </div>
            
            <!-- Card -->
            <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded border border-slate-200 shadow">
                <div class="flex items-center justify-between space-x-2 mb-1">
                    <div class="font-bold text-slate-900">{{ $activity['title'] }}</div>
                    <time class="font-caveat font-medium text-xs text-slate-500">{{ $activity['date']->diffForHumans() }}</time>
                </div>
                <div class="text-slate-500 text-sm">{{ $activity['description'] }}</div>
                <div class="mt-2 text-xs text-slate-400 flex justify-between items-center">
                    <span>by {{ $activity['user'] }}</span>
                    @if($activity['meta'])
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">{{ $activity['meta'] }}</span>
                    @endif
                </div>
            </div>
            
        </div>
    @endforeach
</div>
