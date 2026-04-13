<x-filament-panels::page.simple>
    <div class="relative min-h-[400px] flex flex-col justify-center py-6 sm:py-12">
        <!-- Background decorative elements -->
        <div class="absolute inset-0 bg-gradient-to-tr from-primary-500/10 to-transparent rounded-3xl -z-10 blur-3xl"></div>
        
        <x-filament-panels::form wire:submit="authenticate" wire:key="step-{{ $step }}">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        @if ($step === 2)
            <div class="mt-6 text-center">
                <button 
                    type="button" 
                    wire:click="$set('step', 1)" 
                    class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors duration-200 flex items-center justify-center gap-2 mx-auto"
                >
                    <x-heroicon-m-arrow-right class="w-4 h-4 rtl:rotate-180" />
                    {{ __('Back to login') }}
                </button>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400 font-medium tracking-wider uppercase">
                {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Custom Styles for Premium Look -->
    <style>
        .rtl-input input {
            direction: rtl !important;
            text-align: right !important;
        }
        
        .fi-simple-main {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1) !important;
            border-radius: 1.5rem !important;
        }

        .dark .fi-simple-main {
            background: rgba(24, 24, 27, 0.8) !important;
            border: 1px solid rgba(63, 63, 70, 0.3) !important;
        }

        .fi-simple-header-heading {
            font-size: 2rem !important;
            font-weight: 800 !important;
            background: linear-gradient(to bottom right, var(--primary-500), var(--primary-700));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
    </style>
</x-filament-panels::page.simple>
