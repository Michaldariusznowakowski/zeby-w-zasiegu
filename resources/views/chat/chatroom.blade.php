@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Wiadomości')
@section('api_token', true)
@section('additional_meta')
    <meta name="user_id" content="{{ Auth::id() }}">
    <meta name="recipient_id" content="{{ $recipient->id }}">
    <meta name="chatroom_id" content="{{ $chatroom_id }}">
    <meta name="messages" content="{{ base64_encode(json_encode($messages)) }}">
    <meta name="recipient_name" content="{{ $recipient->name }}">
    <meta name="recipient_surname" content="{{ $recipient->surname }}">
    <meta name="recipient_public_key" content="{{ $recipient->public_key }}">
@endsection
@section('content')
    <a role="button" href="{{ url()->previous() }}">Powrót do poprzedniej strony</a>
    <div class="chat-main">
        <div class="chat-header">
            <h1>
                @if ($offer_link != null)
                    <a href="{{ $offer_link }}">
                @endif
                @if ($offer_photo != null)
                    <img class="profile" src="{{ asset('storage/' . $offer_photo) }}" alt="zdjęcie profilowe">
                @endif
                {{ $recipient->name }} {{ $recipient->surname }}
                @if ($offer_link != null)
                    </a>
                @endif
            </h1>
        </div>
        <div class="chat-messages" aria-busy="true">
            Wczytywanie wiadomości...
        </div>
        <div class="input-message">
            <div class="input-message-container">
                <div class="input-message-editor">
                    <input type="text" placeholder="Wpisz wiadomość" id="input-message" />
                    <button class="button" onclick="sendMessage()">
                        Wyślij</button>
                </div>
                <div class="input-message-attachments">
                    <input type="file" id="input-attachment" />
                    <button class="button" onclick="sendAttachment()">
                        Wyślij załącznik
                    </button>
                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/chat.ts')
@endsection
