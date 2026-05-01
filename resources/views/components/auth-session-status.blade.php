@props([
    'status',
])

@if ($status)
    @if ($status === 'passwords.reset')
        <!-- Password reset success popup (appears for 5 seconds) -->
        <div id="password-reset-popup" class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none" style="display:none;">
            <div class="pointer-events-auto max-w-lg w-full mx-4 bg-white border border-slate-200 rounded-lg shadow-lg p-6 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Selamat!</h3>
                <p class="mt-2 text-sm text-slate-700">Perubahan password baru Anda berhasil dilakukan. Silakan login dengan password baru Anda.</p>
            </div>
        </div>

        <script>
            (function(){
                var el = document.getElementById('password-reset-popup');
                if(!el) return;
                // show with fade-in
                el.style.display = 'flex';
                el.style.opacity = '0';
                el.style.transition = 'opacity 240ms ease-in-out';
                // slight delay to allow transition
                setTimeout(function(){ el.style.opacity = '1'; }, 10);
                // hide after 5 seconds (5000ms)
                setTimeout(function(){ el.style.opacity = '0'; setTimeout(function(){ el.style.display = 'none'; }, 260); }, 5000);
            })();
        </script>
    @else
        <div {{ $attributes->merge(['class' => 'font-medium text-sm text-amber-800']) }}>
            {{ $status }}
        </div>
    @endif
@endif
