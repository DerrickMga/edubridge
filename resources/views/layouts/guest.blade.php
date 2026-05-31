<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EduBridge') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans">
<div class="min-h-screen flex">

    {{-- Left branding panel --}}
    <div class="hidden lg:flex lg:flex-col lg:w-[45%] xl:w-1/2 bg-emerald-800 relative overflow-hidden px-12 py-12">
        {{-- Decorative wave --}}
        <div class="absolute bottom-0 left-0 right-0 opacity-20">
            <svg viewBox="0 0 1440 320" fill="white" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,192L60,176C120,160,240,128,360,138.7C480,149,600,203,720,213.3C840,224,960,192,1080,170.7C1200,149,1320,139,1380,133.3L1440,128L1440,320L0,320Z"/>
            </svg>
        </div>
        <div class="relative flex flex-col flex-1 justify-between">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 w-fit">
                <img src="/logo.svg" alt="EduBridge" class="h-10 w-auto">
            </a>
            {{-- Headline --}}
            <div>
                <h1 class="text-4xl font-extrabold text-white leading-snug mb-4">
                    Quality education,<br>delivered to your<br>phone.
                </h1>
                <p class="text-emerald-200 text-lg leading-relaxed max-w-sm">
                    Zimbabwe's AI-powered learning platform for O&amp;A Level students and teachers.
                </p>
                <div class="mt-8 grid grid-cols-2 gap-3">
                    @foreach([['24/7','AI companion'],['O & A','Level curriculum'],['ZWG','Local payments'],['Free','To get started']] as [$v,$l])
                    <div class="bg-white/10 rounded-xl p-4">
                        <p class="text-white font-extrabold text-xl">{{ $v }}</p>
                        <p class="text-emerald-200 text-xs mt-0.5">{{ $l }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            {{-- Testimonial --}}
            <div class="bg-white/10 rounded-2xl p-5 border border-white/20">
                <p class="text-white text-sm italic leading-relaxed">"EduBridge helped me understand concepts I struggled with for years. Chiedza is like having a patient tutor always available."</p>
                <p class="text-emerald-300 text-xs mt-3 font-semibold">— Form 4 student, Harare</p>
            </div>
        </div>
    </div>

    {{-- Right form panel --}}
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 sm:px-12 bg-slate-50">
        {{-- Mobile logo --}}
        <div class="lg:hidden mb-8 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <img src="/logo.svg" alt="EduBridge" class="h-9 w-auto">
            </a>
        </div>
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
