@extends('layouts.app')

@section('page-title') {{ __('crud.edit', ['item'=>__('beep.hivetype')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.edit', ['item'=>__('beep.hivetype')]) }}
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

            <div class="row">
                <div class="col-md-12">
                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <form method="POST" action="{{ route('hivetype.update',$hivetype->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            {{ csrf_field() }}

                            @include ('hivetype.form', ['submitButtonText' => 'Update'])

                        </form>

                    </div>
                </div>
            </div>

      @endslot
    @endcomponent
@endsection
