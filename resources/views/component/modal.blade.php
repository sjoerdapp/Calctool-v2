{{--
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the Dynq project.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  Dynq
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
--}}

@isset($form)
<form method="POST" action="{{ $form }}" accept-charset="UTF-8">
    {!! csrf_field() !!}
    @endisset

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="Label">@yield('modal_name')</h4>
    </div>

    {{-- Modal body --}}
    <div class="modal-body">
        @yield('modal_content')
    </div>
    {{-- /Modal body --}}

    <div class="modal-footer">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
    </div>

@isset($form)
</form>
@endisset