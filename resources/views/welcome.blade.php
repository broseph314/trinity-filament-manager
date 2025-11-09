<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>HEARTHCORE</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles from Vite -->
    <style>
        {!! Vite::content('resources/css/app.css') !!}
    </style>
    <script>
        {!! Vite::content('resources/js/app.js') !!}
    </script>

    <!-- Tiny extras for glow/embers -->
    <style>
        /* Animated gradient text */
        .ember-gradient {
            background: linear-gradient(90deg, #ff8a3d, #ffcc66, #ff4433, #ff8a3d);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: emberShift 8s ease-in-out infinite;
        }
        @keyframes emberShift {
            0%,100% { background-position: 0% 50%; }
            50%     { background-position: 100% 50%; }
        }

        /* Soft text glow (subtle, respects dark & light) */
        .hc-glow {
            text-shadow:
                0 2px 18px color-mix(in oklab, #ff6a3d 35%, transparent),
                0 1px  3px color-mix(in oklab, #000 25%, transparent);
        }
        .dark .hc-glow {
            text-shadow:
                0 2px 18px color-mix(in oklab, #ffb26b 45%, transparent),
                0 1px  3px color-mix(in oklab, #000 30%, transparent);
        }

        /* Ember particles */
        .embers {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            mask-image: radial-gradient(120% 120% at 50% 100%, #000 60%, transparent 100%);
        }
        .ember {
            position: absolute;
            bottom: -10vh;
            width: 2px; height: 8px;
            border-radius: 1px;
            background: color-mix(in oklab, #ff9f40 70%, #ff4433 30%);
            box-shadow:
                0 0 8px color-mix(in oklab, #ff9f40 50%, transparent),
                0 0 18px color-mix(in oklab, #ff4433 35%, transparent);
            opacity: .6;
            animation: floatUp linear infinite;
            transform: translateZ(0);
        }
        @keyframes floatUp {
            to { transform: translateY(-120vh) translateX(10px) scale(1.1); opacity: 0; }
        }
        /* Respect reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .ember, .ember-gradient { animation: none !important; }
        }
    </style>
</head>
<body class="bg-[#0a0a0a] text-orange-500 antialiased min-h-screen flex flex-col">

<!-- Floating embers -->
<div class="embers" aria-hidden="true">
    @for ($i = 0; $i < 18; $i++)
        @php
            $left = rand(0, 100);
            $delay = rand(0, 800) / 100;   // 0–8s
            $dur =  6 + rand(0, 60) / 10;  // 6–12s
            $w = rand(2, 3);
            $h = rand(6, 12);
            $x = rand(-8, 12);
            $o = (60 + rand(0, 30)) / 100; // .60–.90
        @endphp
        <span class="ember"
              style="
                    left: {{ $left }}%;
                    animation-duration: {{ $dur }}s;
                    animation-delay: {{ $delay }}s;
                    width: {{ $w }}px; height: {{ $h }}px;
                    opacity: {{ $o }};
                    filter: drop-shadow(0 0 10px rgba(255,120,60,.25));
                    transform: translateX({{ $x }}px);
                  "></span>
    @endfor
</div>

<!-- Hero -->
<main class="flex-1 w-full flex items-center justify-center px-6 py-12 ">
    <section class="w-full max-w-5xl grid lg:grid-cols-1 gap-8 items-center">
        <!-- Card -->
        <div class="rounded-xl border border-orange-500 dark:border-zinc-700 bg-zinc-900/60 backdrop-blur p-8 lg:p-12 shadow-[0_10px_30px_-12px_rgba(0,0,0,.25)]">
            <p class="uppercase tracking-widest text-xs text-zinc-600 dark:text-zinc-400 mb-3">TrinityCore Companion</p>

            <h1 class="hc-glow ember-gradient text-6xl leading-tight font-semibold mb-2">HEARTHCORE</h1>

            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                       class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 border border-transparent bg-black text-white dark:bg-white dark:text-black hover:opacity-90 transition">
                        Enter the Realm
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 border border-orange-500 bg-black text-orange-500 hover:opacity-90 transition">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 border border-orange-500 dark:border-zinc-700 hover:bg-zinc-800/5 dark:hover:bg-white/5 transition">
                            Create account
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Tiny feature chips -->
            <div class="mt-6 flex flex-wrap gap-2 text-xs text-orange-500">
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-500 dark:border-zinc-700 px-3 py-1">Filament v4</span>
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-500 dark:border-zinc-700 px-3 py-1">Tailwind v4</span>
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-500 dark:border-zinc-700 px-3 py-1">TrinityCore tools</span>
            </div>
        </div>
    </section>
</main>

<!-- Footer -->
<footer class="w-full max-w-5xl mx-auto px-6 pb-8 text-xs text-zinc-500 dark:text-zinc-400">
    <div class="flex items-center justify-between">
        <span>&copy; {{ date('Y') }} Jackistan - MIDNIGHT ARRIVES</span>
        <span class="hidden sm:block">It costs nothing to be nice</span>
    </div>
</footer>
</body>
</html>
