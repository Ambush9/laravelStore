@extends('layouts.master')

@section('title', 'Товар')

@section('content')
        <h1>{{ $product->name }}</h1>
        <h2>{{ $product->category->name }}</h2>
        <h2>Мобильные телефоны</h2>
        <p>Price: <b>{{ $product->price }} ₽</b></p>
        <img src="{{ Storage::url($product->image) }}">
        <p>{{ $product->description }}</p>
            @if($product->isAvailable())
                <form action="{{ route('basket-add', $product) }}" method="POST">
                <button type="submit" class="btn btn-success" role="button">Добавить в корзину</button>
                @csrf
                @else
                <span>Не доступен</span>
                <br>
                <span>Сообщить мне когда товар появиться в наличии</span>
                <div class="warning">
                    @if($errors->get('email'))
                        {!! $errors->get('email')[0]  !!}
                    @endif
                </div>
                <form method="POST" action="{{ route('subscription', $product) }}">
                    @csrf
                    <input type="text" name="email">
                    <button type="submit">Отправить</button>
                </form>
            @endif
        </form>
@endsection
