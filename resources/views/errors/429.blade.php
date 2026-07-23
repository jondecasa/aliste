@extends('errors.layout')

@section('titulo', 'Demasiadas peticiones')
@section('codigo', '429')
@section('mensaje', 'Has hecho demasiadas peticiones en poco tiempo. Espera un momento e inténtalo de nuevo.')
