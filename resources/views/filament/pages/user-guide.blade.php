<x-filament-panels::page>
    <div class="space-y-6">
        @if($activeFile)
            <div class="flex items-center justify-between">
                <button wire:click="closeGuide" class="flex items-center gap-2 text-primary-600 font-bold hover:gap-3 transition-all">
                    <x-heroicon-o-arrow-left class="w-5 h-5" /> Back to Dashboard
                </button>
                <div class="px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-[10px] font-mono text-gray-400">
                    {{ $activeFile }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-8 shadow-sm">
                <article class="prose prose-slate dark:prose-invert max-w-none 
                    prose-headings:font-black prose-headings:tracking-tight
                    prose-h1:text-4xl prose-h1:text-primary-600 prose-h1:mb-8
                    prose-h2:text-2xl prose-h2:text-gray-900 dark:prose-h2:text-white prose-h2:mt-12 prose-h2:border-b prose-h2:pb-4
                    prose-p:text-lg prose-p:leading-relaxed prose-p:text-gray-600 dark:prose-p:text-gray-400
                    prose-ul:list-disc prose-ul:pl-6
                    prose-li:text-gray-600 dark:prose-li:text-gray-400
                    prose-pre:bg-gray-900 prose-pre:rounded-xl prose-pre:p-6
                    prose-code:text-primary-600 prose-code:bg-primary-50 dark:prose-code:bg-primary-900/30 prose-code:rounded prose-code:px-1
                ">
                    {!! $guideContent !!}
                </article>
            </div>
        @else
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 p-8 text-white shadow-xl">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight">Welcome, {{ auth()->user()->name }}!</h2>
                        <p class="mt-2 text-primary-100 max-w-xl text-lg leading-relaxed">
                            This guide is tailored specifically for your role. Follow these steps to maximize your efficiency within the CRM ecosystem.
                        </p>
                    </div>
                    <div class="hidden lg:block opacity-20 transform translate-x-10">
                        <x-heroicon-o-academic-cap class="w-48 h-48" />
                    </div>
                </div>
                
                {{-- Decorative circles --}}
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-10 -top-10 w-48 h-48 bg-primary-400/20 rounded-full blur-2xl"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @foreach($this->getRoleGuides() as $guide)
                    <div class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-3 rounded-xl bg-{{ $guide['color'] }}-50 dark:bg-{{ $guide['color'] }}-900/30 text-{{ $guide['color'] }}-600 transition-transform group-hover:scale-110">
                                @svg($guide['icon'], 'w-8 h-8')
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $guide['title'] }}</h3>
                        </div>

                        <ul class="space-y-4">
                            @foreach($guide['steps'] as $index => $step)
                                <li class="flex gap-4 group/item">
                                    <span class="flex-none flex items-center justify-center w-6 h-6 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-400 text-xs font-bold group-hover/item:bg-{{ $guide['color'] }}-100 dark:group-hover/item:bg-{{ $guide['color'] }}-900/50 group-hover/item:text-{{ $guide['color'] }}-600 transition-colors">
                                        {{ $index + 1 }}
                                    </span>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed group-hover/item:text-gray-900 dark:group-hover/item:text-gray-100 transition-colors">
                                        {{ $step }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-800">
                            <span class="text-{{ $guide['color'] }}-600 font-semibold text-xs uppercase tracking-wider flex items-center gap-2">
                               Module Optimized <span class="w-1.5 h-1.5 rounded-full bg-{{ $guide['color'] }}-500 animate-pulse"></span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                @foreach($this->getSystemDocs() as $doc)
                    <button wire:click="openGuide('{{ $doc['file'] }}')" class="flex items-center text-left gap-3 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 hover:border-primary-500 transition-all group shadow-sm">
                        <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-400 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                            @svg($doc['icon'], 'w-5 h-5')
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-gray-900 dark:text-white">{{ $doc['title'] }}</h5>
                            <p class="text-[10px] text-gray-400 font-mono">{{ $doc['file'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 border border-dashed border-gray-200 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-4 mt-8">
            <div class="flex items-center gap-4 text-center md:text-left">
                <div class="flex-none p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-lg">
                    <x-heroicon-o-question-mark-circle class="w-6 h-6" />
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white text-lg">Still need help?</h4>
                    <p class="text-gray-500 text-sm">Our support team is available mon-fri, 9am - 6pm.</p>
                </div>
            </div>
            <a href="mailto:support@viding.co" class="px-6 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl font-bold hover:bg-black dark:hover:bg-gray-100 transition-all shadow-md">
                Contact Support
            </a>
        </div>
    </div>
</x-filament-panels::page>
