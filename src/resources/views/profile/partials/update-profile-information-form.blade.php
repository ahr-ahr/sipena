<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if ($user->tipe_user === \App\Enums\UserType::SISWA)
            <div>
                <x-input-label value="Nama Lengkap" />
                <x-text-input name="nama"
                    class="mt-1 block w-full"
                    :value="old('nama', $user->siswaProfile?->nama)"
                    required />
                <x-input-error :messages="$errors->get('nama')" />
            </div>

            <div>
                <x-input-label value="NIS" />
                <x-text-input name="nis"
                    class="mt-1 block w-full"
                    :value="old('nis', $user->siswaProfile?->nis)" />
            </div>

            <div>
                <x-input-label value="Kelas" />
                <select name="kelas_id"
                    class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">— Pilih Kelas —</option>

                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}"
                            @selected(
                                old('kelas_id', $user->siswaProfile?->kelas_id) == $kelas->id
                            )>
                            {{ $kelas->nama }}
                        </option>
                    @endforeach
                </select>

                <x-input-error :messages="$errors->get('kelas_id')" />
            </div>
        @endif


        @if ($user->tipe_user === \App\Enums\UserType::PEGAWAI)
            <div>
                <x-input-label value="Nama Lengkap" />
                <x-text-input name="nama"
                    class="mt-1 block w-full"
                    :value="old('nama', $user->pegawaiProfile?->nama)"
                    required />
                <x-input-error :messages="$errors->get('nama')" />
            </div>

            <div>
                <x-input-label value="NIP" />
                <x-text-input name="nip"
                    class="mt-1 block w-full"
                    :value="old('nip', $user->pegawaiProfile?->nip)" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
