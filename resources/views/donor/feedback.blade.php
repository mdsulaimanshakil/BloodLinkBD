<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rate This Donor — {{ config('app.name', 'BloodLinkBD') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Figtree', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; flex-direction: column; }
        .page-center { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px 16px; }

        .feedback-card {
            width: 100%; max-width: 500px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(12px);
        }

        .star-row {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 24px 0;
        }

        .star-label {
            cursor: pointer;
            font-size: 2.5rem;
            color: #334155;
            transition: color 0.15s, transform 0.15s;
            user-select: none;
        }

        .star-label:hover,
        .star-label.active { color: #fbbf24; transform: scale(1.15); }

        /* When hovering, highlight all stars up to that one */
        .star-row:hover .star-label { color: #fbbf24; }
        .star-row .star-label:hover ~ .star-label { color: #334155; }

        .star-input { display: none; }

        .rating-text {
            text-align: center;
            font-size: 0.875rem;
            color: #94a3b8;
            min-height: 20px;
            transition: all 0.2s;
        }

        .notes-area {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #e2e8f0;
            padding: 12px;
            font-family: 'Figtree', sans-serif;
            font-size: 0.875rem;
            resize: vertical;
            min-height: 100px;
            transition: border-color 0.2s;
        }
        .notes-area:focus { outline: none; border-color: rgba(220,38,38,0.5); }
        .notes-area::placeholder { color: #475569; }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(220,38,38,0.3);
        }
        .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(220,38,38,0.4); }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .info-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.25);
            color: #fca5a5; padding: 6px 14px; border-radius: 100px;
            font-size: 0.8rem; font-weight: 600;
        }

        .back-link { color: #64748b; text-decoration: none; font-size: 0.875rem; transition: color 0.2s; }
        .back-link:hover { color: #e2e8f0; }
    </style>
</head>
<body>
<div class="page-center">
    <div class="feedback-card">
        <div class="text-center mb-6">
            <div style="font-size:3rem; margin-bottom:12px;">🩸</div>
            <h1 class="text-xl font-bold text-white">Rate This Donor</h1>
            <p class="text-slate-400 text-sm mt-2">
                Your feedback helps build trust in the BloodLinkBD community.
            </p>

            @if ($donationHistory->donor)
                <div class="mt-4">
                    <span class="info-pill">
                        Donor: {{ $donationHistory->donor->name }}
                    </span>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('feedback.submit', $donationHistory) }}" id="feedback-form">
            @csrf

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-semibold text-slate-300 text-center mb-2">How was your experience? *</label>

                <div class="star-row" id="star-row">
                    @for ($i = 1; $i <= 5; $i++)
                        <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}"
                               class="star-input" {{ old('rating') == $i ? 'checked' : '' }}>
                        <label for="star{{ $i }}" class="star-label {{ old('rating') >= $i ? 'active' : '' }}"
                               data-value="{{ $i }}">★</label>
                    @endfor
                </div>

                <div id="rating-text" class="rating-text">Click a star to rate</div>

                @error('rating')
                    <p class="text-red-400 text-xs text-center mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div class="mt-6">
                <label for="feedback_notes" class="block text-sm font-semibold text-slate-300 mb-2">
                    Notes <span class="text-slate-500 font-normal">(optional)</span>
                </label>
                <textarea id="feedback_notes" name="feedback_notes" rows="4"
                          class="notes-area"
                          placeholder="How was the donor's communication, punctuality, and willingness to help?">{{ old('feedback_notes') }}</textarea>
                @error('feedback_notes')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="mt-6">
                <button type="submit" id="submit-btn" class="submit-btn" disabled>
                    Submit Feedback
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('donor.dashboard') }}" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
    const ratingLabels = {
        1: 'Poor — Not satisfied',
        2: 'Fair — Below expectations',
        3: 'Good — Met expectations',
        4: 'Great — Above expectations',
        5: 'Excellent — Outstanding experience!',
    };

    const stars     = document.querySelectorAll('.star-label');
    const inputs    = document.querySelectorAll('.star-input');
    const ratingEl  = document.getElementById('rating-text');
    const submitBtn = document.getElementById('submit-btn');

    let currentRating = {{ old('rating', 0) }};

    function updateStars(rating) {
        stars.forEach((star, i) => {
            star.classList.toggle('active', i < rating);
        });
        ratingEl.textContent = ratingLabels[rating] || 'Click a star to rate';
        submitBtn.disabled = rating < 1;
    }

    // Hover effect
    stars.forEach((star, idx) => {
        star.addEventListener('mouseenter', () => updateStars(idx + 1));
        star.addEventListener('click', () => {
            currentRating = idx + 1;
            inputs[idx].checked = true;
            updateStars(currentRating);
        });
    });

    document.getElementById('star-row').addEventListener('mouseleave', () => {
        updateStars(currentRating);
    });

    // Init if there's an old value
    if (currentRating > 0) updateStars(currentRating);
</script>
</body>
</html>
