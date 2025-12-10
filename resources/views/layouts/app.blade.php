<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DispoDialyse') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen">
        <!-- Navigation principale -->
        @include('layouts.components.navigation')

        <!-- Sidebar mobile -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 lg:hidden"
             style="display: none;">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" aria-hidden="true"></div>
        </div>

        <div class="flex">
            <!-- Sidebar desktop -->
            @include('layouts.components.sidebar')

            <!-- Contenu principal -->
            <div class="flex-1 lg:ml-64">
                <!-- Header avec breadcrumbs -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="px-4 sm:px-6 lg:px-8 py-4">
                        @yield('header')
                    </div>
                </header>

                <!-- Messages flash -->
                @if (session('success'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg" role="alert">
                            <div class="flex">
                                <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg" role="alert">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg" role="alert">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm text-yellow-800 font-medium">{{ session('warning') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Contenu de la page -->
                <main class="px-4 sm:px-6 lg:px-8 py-8">
                    @yield('content')
                </main>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 mt-12">
                    <div class="px-4 sm:px-6 lg:px-8 py-4">
                        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
                            <p>© {{ date('Y') }} DispoDialyse. Tous droits réservés.</p>
                            <p class="mt-2 md:mt-0">
                                Version 1.0.0 | 
                                <a href="#" class="text-blue-600 hover:text-blue-800">Documentation</a> | 
                                <a href="#" class="text-blue-600 hover:text-blue-800">Support</a>
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>