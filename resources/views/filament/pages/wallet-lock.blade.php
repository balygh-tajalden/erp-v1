<div class="flex items-center justify-center min-h-[70vh] bg-transparent">
    <div class="relative p-10 bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl w-full max-w-md group overflow-hidden transition-all duration-500 hover:shadow-cyan-500/20">
        <!-- Background Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-cyan-500/20 rounded-full blur-3xl group-hover:bg-cyan-500/30 transition-all duration-500"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/20 rounded-full blur-3xl group-hover:bg-purple-500/30 transition-all duration-500"></div>

        <div class="relative z-10 text-center">
            <div class="mb-8 flex justify-center">
                <div class="p-4 bg-white/5 rounded-2xl border border-white/10 ring-1 ring-white/20 shadow-inner">
                    <svg class="w-12 h-12 text-cyan-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-white mb-2 tracking-tight">الواجهة محمية</h2>
            <p class="text-gray-400 text-sm mb-8 leading-relaxed">يرجى إدخال الرمز السري للوصول إلى حركات المحفظة</p>

            <div class="space-y-6">
                <input 
                    type="password" 
                    wire:model="unlockCode" 
                    placeholder="••••" 
                    maxlength="4"
                    class="block w-full px-6 py-4 text-center text-3xl tracking-[1.5em] bg-white/5 border border-white/10 rounded-2xl text-white placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-300"
                    autofocus
                >

                <button 
                    wire:click="unlock"
                    class="w-full py-4 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold rounded-2xl shadow-lg shadow-cyan-900/20 active:scale-[0.98] transition-all duration-200 uppercase tracking-wider"
                >
                    فتح الواجهة
                </button>
                
                @if (session()->has('error'))
                    <p class="text-red-400 text-sm mt-4 animate-bounce font-medium">{{ session('error') }}</p>
                @endif
            </div>
        </div>

        <!-- Glass micro-texture overlay -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.03] bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
    </div>
</div>

<style>
    /* Add subtle glassmorphism animations */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .backdrop-blur-xl {
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }
</style>
