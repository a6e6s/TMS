<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <meta name="description" content="Collect bug reports and support tickets from your web applications in one place, and push them to your project management system.">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.getItem('tms-theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <header class="border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="{{ url('/') }}" class="flex items-center gap-2 font-semibold">
                    <img src="{{ asset('images/TMS.png') }}" alt="TMS" class="h-10 w-auto">
                </a>
                <div class="flex items-center gap-3 text-sm">
                    <a href="#features" class="hidden px-3 py-2 text-slate-600 hover:text-slate-900 sm:inline dark:text-slate-300 dark:hover:text-white">Features</a>
                    <a href="#how-it-works" class="hidden px-3 py-2 text-slate-600 hover:text-slate-900 sm:inline dark:text-slate-300 dark:hover:text-white">How it works</a>
                    <button type="button" id="theme-toggle" aria-label="Toggle dark mode" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                        <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                        <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>
                    <a href="{{ url('/admin') }}" class="rounded-lg bg-violet-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-violet-500">Sign in</a>
                </div>
            </nav>
        </header>

        <main>
            <section class="mx-auto max-w-6xl px-6 py-20 text-center lg:py-28">
                <p class="mb-4 inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                    Ticketing Management System
                </p>
                <h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">
                    Every bug report from every app, in one place.
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                    TMS receives tickets from your web applications with the browser details and screenshots already attached, then tracks them to resolution and pushes them into your project management system.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ url('/admin') }}" class="w-full rounded-lg bg-violet-600 px-6 py-3 font-medium text-white shadow-sm hover:bg-violet-500 sm:w-auto">Open the admin panel</a>
                    <a href="https://github.com/a6e6s/TMS" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 hover:bg-slate-100 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">View on GitHub</a>
                </div>
            </section>

            <section id="features" class="border-t border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900">
                <div class="mx-auto max-w-6xl px-6">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight">Everything a support queue needs</h2>
                        <p class="mt-4 text-slate-600 dark:text-slate-400">Built for teams that run several web applications and want one queue for all of them.</p>
                    </div>
                    <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @php
                            $features = [
                                ['Ticket intake API', 'Each source app connects with its own token. Tickets arrive through a single authenticated endpoint.'],
                                ['Browser and reporter details', 'Every ticket keeps the user agent, viewport, and page URL, so reproducing a bug starts with facts.'],
                                ['Screenshot gallery', 'Screenshots are stored with the ticket and shown in a gallery on the ticket view.'],
                                ['Comments and follow-up', 'Staff comment on tickets, and submitters can follow up through the client API.'],
                                ['Project management push', 'Send tickets to your PMS as tasks, and keep their status in sync through webhooks.'],
                                ['Roles and permissions', 'Control who can see and change tickets with role-based access in the admin panel.'],
                            ];
                        @endphp
                        @foreach ($features as [$title, $body])
                            <div class="rounded-xl border border-slate-200 p-6 dark:border-slate-800">
                                <h3 class="font-semibold">{{ $title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $body }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="mx-auto max-w-6xl px-6 py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight">How it works</h2>
                    <p class="mt-4 text-slate-600 dark:text-slate-400">Three steps from a bug report to a tracked task.</p>
                </div>
                <ol class="mt-14 grid gap-8 md:grid-cols-3">
                    <li class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                        <span class="text-sm font-semibold text-violet-600 dark:text-violet-400">01</span>
                        <h3 class="mt-2 font-semibold">Connect a source app</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Create a source app in TMS and generate its token. Add the plugin, or call the API from your own code.</p>
                    </li>
                    <li class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                        <span class="text-sm font-semibold text-violet-600 dark:text-violet-400">02</span>
                        <h3 class="mt-2 font-semibold">Receive and triage</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Tickets arrive with their context. Your team reviews, assigns, and comments on them in one list.</p>
                    </li>
                    <li class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                        <span class="text-sm font-semibold text-violet-600 dark:text-violet-400">03</span>
                        <h3 class="mt-2 font-semibold">Track to resolution</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">Push tickets to your project management system and follow their status until they are closed.</p>
                    </li>
                </ol>
            </section>

            <section class="px-6 pb-20">
                <div class="mx-auto flex max-w-6xl flex-col items-center gap-6 rounded-2xl bg-violet-600 px-8 py-12 text-center text-white sm:px-12">
                    <h2 class="text-2xl font-bold sm:text-3xl">Ready to see your tickets in one place?</h2>
                    <a href="{{ url('/admin') }}" class="rounded-lg bg-white px-6 py-3 font-medium text-violet-700 shadow-sm hover:bg-violet-50">Sign in to TMS</a>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 dark:border-slate-800">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-6 py-6 text-sm text-slate-500 sm:flex-row">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
                <span>Built with Laravel and Filament. Released under the MIT license.</span>
            </div>
        </footer>

        <script>
            document.getElementById('theme-toggle').addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('tms-theme', isDark ? 'dark' : 'light');
            });
        </script>
    </body>
</html>
