@extends('layouts.app')

@section('content')
    <div class="min-h-full flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Please sign in to access the admin panel.') }}
            </div>
        
            <x-validation-errors class="mb-4" />
        
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label class="block font-medium text-sm text-gray-700" for="email">
                        {{ __('Email') }}
                    </label>
                    <input class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" id="email" type="email" name="email" required="required" autofocus="autofocus">
                </div>

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700" for="password">
                        {{ __('Password') }}
                    </label>
                    <input class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" id="password" type="password" name="password" required="required" autocomplete="current-password">
                </div>

                <div class="block mt-4">
                    <label for="remember_me" class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="remember_me" name="remember">
                        <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 ml-4">
                        {{ __('Log in') }}
                    </button>                    
                </div>

                <a href="/forgot-password" class="underline text-sm text-gray-600 hover:text-gray-900" >Forgot password?</a>
            </form>
        </div>

        <!-- Demo Credentials Section -->
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-blue-50 border border-blue-200 shadow-md overflow-hidden sm:rounded-lg">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">Demo Credentials</h3>
            <p class="text-sm text-blue-700 mb-4">Use these credentials to test different user roles:</p>

            <div class="space-y-4">
                <!-- Admin Users -->
                <div>
                    <h4 class="font-medium text-blue-800 mb-2">🔧 Administrative Users</h4>
                    <div class="space-y-2 text-sm">
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">System Administrator</div>
                            <div class="text-gray-600">Email: <span class="font-mono">admin@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Maintenance Manager</div>
                            <div class="text-gray-600">Email: <span class="font-mono">manager@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                    </div>
                </div>

                <!-- Technicians -->
                <div>
                    <h4 class="font-medium text-blue-800 mb-2">🔨 Maintenance Technicians</h4>
                    <div class="space-y-2 text-sm">
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">John Smith</div>
                            <div class="text-gray-600">Email: <span class="font-mono">john.smith@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Sarah Johnson</div>
                            <div class="text-gray-600">Email: <span class="font-mono">sarah.johnson@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Mike Wilson</div>
                            <div class="text-gray-600">Email: <span class="font-mono">mike.wilson@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Emma Davis</div>
                            <div class="text-gray-600">Email: <span class="font-mono">emma.davis@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                    </div>
                </div>

                <!-- Facility Managers -->
                <div>
                    <h4 class="font-medium text-blue-800 mb-2">🏢 Facility Managers</h4>
                    <div class="space-y-2 text-sm">
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Robert Brown</div>
                            <div class="text-gray-600">Email: <span class="font-mono">robert.brown@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Lisa Anderson</div>
                            <div class="text-gray-600">Email: <span class="font-mono">lisa.anderson@liberu.co.uk</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                        </div>
                    </div>
                </div>

                <!-- Demo Role Users -->
                <div>
                    <h4 class="font-medium text-blue-800 mb-2">👥 Demo Role Users</h4>
                    <div class="space-y-2 text-sm">
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Demo Tenant</div>
                            <div class="text-gray-600">Email: <span class="font-mono">tenant@demo.com</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">demo123</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Demo Contractor</div>
                            <div class="text-gray-600">Email: <span class="font-mono">contractor@demo.com</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">demo123</span></div>
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <div class="font-medium text-gray-900">Demo Landlord</div>
                            <div class="text-gray-600">Email: <span class="font-mono">landlord@demo.com</span></div>
                            <div class="text-gray-600">Password: <span class="font-mono">demo123</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-xs text-yellow-800">
                    <strong>Note:</strong> These are demo credentials for testing purposes. 
                    In production, remove this section and use secure passwords.
                </p>
            </div>
        </div>
    </div>
@endsection
