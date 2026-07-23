@extends('errors.layout')

@section('titulo', 'No se puede completar la solicitud')
@section('codigo', $exception->getStatusCode() ?? '400')
@section('mensaje', 'Ha habido un problema con tu solicitud. Vuelve al inicio e inténtalo de nuevo.')
