<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Hero / Intro Section -->
        <x-filament::section>
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="prose dark:prose-invert max-w-none lg:col-span-2">
                    <h3 class="text-xl font-bold text-gray-950 dark:text-white">
                        Empowering Youth Governance
                        <br>
                        </br>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        SKPrimeTech is a comprehensive management system built specifically for Sangguniang Kabataan (SK) Federations and local SK councils.
                        It streamlines reporting, improves transparency, and helps youth leaders manage submissions and data consistently across municipalities and barangays.
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        The platform is designed to feel familiar inside the admin panel, while keeping workflows simple, guided, and audit-friendly.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-[#1A508E]/10 dark:bg-[#1A508E]/20 rounded-lg">
                                <x-heroicon-o-users class="w-5 h-5 text-[#1A508E] dark:text-blue-400" />
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-950 dark:text-white">
                                    Designed for
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Federations, Municipalities, & Barangays
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-[#1A508E]/10 dark:bg-[#1A508E]/20 rounded-lg">
                                <x-heroicon-o-check-badge class="w-5 h-5 text-[#1A508E] dark:text-blue-400" />
                            </div>
                            <div class="text-sm font-medium text-gray-950 dark:text-white">
                                Core Outcomes
                            </div>
                        </div>
                        <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-400 ml-1">
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#1A508E]"></span>
                                <span>Faster report submissions</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#1A508E]"></span>
                                <span>Clear compliance visibility</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#1A508E]"></span>
                                <span>Centralized data management</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- What We Provide -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-gray-500" />
                        <span>Structured Reporting</span>
                    </div>
                </x-slot>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Standardized forms and submissions that make reporting consistent and easier to review across all levels of governance.
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-500" />
                        <span>Compliance Monitoring</span>
                    </div>
                </x-slot>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Real-time visibility into status and completion so leaders can act early and ensure no barangay is left behind.
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-user-group class="w-5 h-5 text-gray-500" />
                        <span>SK Profiling</span>
                    </div>
                </x-slot>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Organized records for SK officials and local units to support continuity, accountability, and proper data management.
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-server class="w-5 h-5 text-gray-500" />
                        <span>Centralized Data</span>
                    </div>
                </x-slot>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    One secure place for submissions and reference data across municipalities and barangays, accessible anytime.
                </p>
            </x-filament::section>
        </div>

        <!-- History & Vision -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">
                    Our History
                </x-slot>
                <div class="prose dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-400">
                    <p>
                        Established in 2025, SK PRIME began as a simple "prime" concept that explored ways to respond to the growing need for a more efficient and reliable system in monitoring and evaluating the performance of Sangguniang Kabataan (SK) councils across Catanduanes.
                    </p>
                    <p class="mt-4">
                        In early September 2025, this idea was first examined through a consultative meeting between the SKPF President and the DILG Catanduanes, where key concerns and operational challenges were identified. Within the same month, the concept was formally presented to the DILG and received an affirmative response, marking its transition from an initial exploration into a supported initiative.
                    </p>
                    <p class="mt-4">
                        On September 20, 2025, during a regular SKPF meeting, the concept of a performance monitoring system was introduced to the council, and the body collectively defined its direction and purpose. By October 25, 2025, the system had taken clearer form as the council developed its indicators, Means of Verification (MOVs), timelines, and an incentivization scheme for best practices and PPAs, while also outlining its dissemination through SKMF Presidents.
                    </p>
                    <p class="mt-4">
                        The initiative was further strengthened on November 15, 2025, with a renewed emphasis on its implementation and significance. On December 14, 2025, SK PRIME, or Performance Rating, Integrity, and Monitoring for Excellence, was formally launched, followed by its presentation during the Year-End Assembly on December 20 to 21, 2025, where it was prepared for full implementation beginning January 2026.
                    </p>
                    <p class="mt-4">
                        This progression culminated on January 5, 2026, with the signing of a memorandum among DILG Catanduanes, PYDO, and SKPF, formally institutionalizing the system. From its beginnings as a "prime" concept, the initiative continued to evolve through innovation and collaboration, eventually leading to the development of SKPrimeTech, a more advanced and systematized digital platform designed to modernize SK governance and ensure a more responsive, data-driven approach to youth leadership.
                    </p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Mission & Vision
                </x-slot>
                <div class="space-y-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-950 dark:text-white">Mission</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            To digitize and modernize Sangguniang Kabataan operations, fostering efficiency and accountability.
                        </p>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-950 dark:text-white">Vision</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                        To become a leading provincial youth governance monitoring system that fosters transparency, accountability, and excellence among Sangguniang Kabataan, empowering youth institutions to deliver measurable, inclusive, and impactful programs for their communities.
                        </p>
                    </div>
                    <div class="pt-2">
                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Key Focus Areas
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-check class="w-4 h-4 text-[#1A508E]" />
                                <span>Automated Reporting</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-check class="w-4 h-4 text-[#1A508E]" />
                                <span>Compliance Monitoring</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-check class="w-4 h-4 text-[#1A508E]" />
                                <span>Digital Profiling</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-check class="w-4 h-4 text-[#1A508E]" />
                                <span>Data Management</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- System Information & Technical Stack -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-cpu-chip class="w-5 h-5 text-gray-500" />
                        <span>System Information</span>
                    </div>
                </x-slot>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-white/5">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">System Name</span>
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">SKPrimeTech</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-white/5">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">System Version</span>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-medium text-[#1A508E] bg-[#1A508E]/10 rounded-full dark:text-blue-400 dark:bg-blue-400/10">v{{ config('app.version') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-white/5">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Developer</span>
                        <a href="https://github.com/rjmolina13" target="_blank" class="flex items-center gap-1 text-sm font-medium text-[#1A508E] hover:underline dark:text-blue-400">
                            <span>@rjmolina13</span>
                            <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                        </a>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">License</span>
                        <span class="text-sm font-medium text-gray-950 dark:text-white">Proprietary</span>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-code-bracket-square class="w-5 h-5 text-gray-500" />
                        <span>Technical Stack</span>
                    </div>
                </x-slot>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Framework</div>
                        <div class="font-semibold text-gray-950 dark:text-white">Laravel 12</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Admin Panel</div>
                        <div class="font-semibold text-gray-950 dark:text-white">Filament v5</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Language</div>
                        <div class="font-semibold text-gray-950 dark:text-white">PHP 8.4</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Styling</div>
                        <div class="font-semibold text-gray-950 dark:text-white">Tailwind CSS v4</div>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Built with modern web technologies to ensure performance, security, and scalability.
                </div>
            </x-filament::section>
        </div>

        <!-- Contact Footer -->
        <x-filament::section>
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                Get in touch
            </h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-white/5 dark:bg-white/5 md:col-span-1">
                    <div class="mb-1 flex items-center gap-2 text-sm font-medium text-gray-950 dark:text-white">
                        <x-heroicon-o-envelope class="h-5 w-5 text-gray-500" />
                        <span>Email</span>
                    </div>
                    <a href="mailto:dev@rubyj.xyz" class="text-sm text-gray-600 transition-colors hover:text-[#1A508E] dark:text-gray-400">
                        dev@rubyj.xyz
                    </a>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-white/5 dark:bg-white/5 md:col-span-1">
                    <div class="mb-1 flex items-center gap-2 text-sm font-medium text-gray-950 dark:text-white">
                        <x-heroicon-o-phone class="h-5 w-5 text-gray-500" />
                        <span>Phone Number</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">+63 912 345 6789</p>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-white/5 dark:bg-white/5 md:col-span-2">
                    <div class="mb-1 flex items-center gap-2 text-sm font-medium text-gray-950 dark:text-white">
                        <x-heroicon-o-map-pin class="h-5 w-5 text-gray-500" />
                        <span>Address</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Room 13, Sangguniang Panlawigan, Provincial Capitol Building, Francia, Virac, Catanduanes, Philippines
                    </p>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-white/5 dark:bg-white/5 md:col-span-4">
                    <div class="mb-3 text-sm font-medium text-gray-950 dark:text-white">Facebook</div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                        <a href="https://www.facebook.com/SKCatanduanes" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-2 text-sm text-gray-600 ring-1 ring-gray-950/5 transition-all hover:bg-white hover:text-[#1877F2] dark:bg-gray-800 dark:text-gray-400 dark:ring-white/10" aria-label="Facebook">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                            </svg>
                            <span>facebook.com/SKCatanduanes</span>
                        </a>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
