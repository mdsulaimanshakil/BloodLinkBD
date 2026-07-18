@if ($requests->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">🩸</div>
        <h3 class="text-lg font-semibold text-slate-300">No active requests</h3>
        <p class="text-slate-500 mt-1 text-sm">All clear! No urgent blood requests at this time.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($requests as $request)
            <div class="request-card urgency-{{ $request->urgency }}">
                {{-- Top row: urgency + blood type --}}
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="urgency-badge urgency-{{ $request->urgency }}">
                            @if($request->urgency === 'critical') 🔴 @elseif($request->urgency === 'urgent') 🟠 @else 🟢 @endif
                            {{ ucfirst($request->urgency) }}
                        </span>
                    </div>
                    <div class="blood-badge">{{ $request->blood_group }}</div>
                </div>

                {{-- Patient + hospital --}}
                <h3 class="font-semibold text-white text-base leading-snug">
                    {{ $request->patient_name }}
                </h3>
                <p class="text-slate-400 text-sm mt-0.5">🏥 {{ $request->hospital }}</p>
                <p class="text-slate-500 text-sm">📍 {{ $request->district }}</p>

                @if ($request->additional_notes)
                    <p class="text-slate-400 text-xs mt-2 italic line-clamp-2">
                        "{{ $request->additional_notes }}"
                    </p>
                @endif

                {{-- Footer --}}
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                    <div class="flex items-center gap-3">
                        <span class="stat-pill">
                            🕐 {{ $request->created_at->diffForHumans() }}
                        </span>
                        @if ($request->expires_at)
                            <span class="countdown-timer">
                                Expires {{ $request->expires_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('blood-requests.show', $request) }}" class="view-detail-link">
                        View Details →
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
