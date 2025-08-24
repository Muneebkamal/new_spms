@extends('layouts.app')

@section('content')
<section>
    <div class="container bg-white pr-4 pl-4 log_section pb-5 pt-lg-4">
        @if(auth()->user()->role == 'admin')
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end">
                    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addLinkModal">
                        Add Link
                    </button>
                </div>
            </div>
        @endif

        @foreach ($links as $link)
            <a href="{{ $link->url }}" target="_blank" class="text-white mt-4 d-block">
                <div class="row">
                    <div class="col-lg-12 col-12 shadow p-5 text-center" style="background: {{ $link->color }};">
                        <h3>{{ $link->label }}</h3> 
                        @if(auth()->user()->role == 'admin')
                            <a href="#" 
                                class="btn btn-primary edit-btn"
                                data-id="{{ $link->id }}"
                                data-label="{{ $link->label }}"
                                data-url="{{ $link->url }}"
                                data-color="{{ $link->color }}">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach

    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="addLinkModal" tabindex="-1" role="dialog" aria-labelledby="addLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="addLinkModalLabel">Add Link</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="linkForm">
                @csrf
                <input type="hidden" id="form_id" name="id">
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="color" class="form-control" id="color" name="color" value="#28a745">
                    </div>

                    <div class="form-group">
                        <label for="label">Label</label>
                        <input type="text" class="form-control" id="label" name="label" required>
                    </div>

                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="text" class="form-control" id="url" name="url" required>
                    </div>
                    
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
            
        </div>
    </div>
</div>


<section class="p-2 mt-2" style="background-color: #fff;">
    <footer>
        <div class="container social_icon text-center">
            <hr class="font-weight-bold">
            <small class="text-center text-muted">Copyright 2024</small>
        </div>
    </footer>
</section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            // Open modal for create
            $('[data-target="#addLinkModal"]').on('click', function(){
                $('#linkForm')[0].reset();   // clear form
                $('#form_id').val('');       // empty hidden ID
                $('#addLinkModalLabel').text('Add Link');
                $('#saveBtn').text('Save');
            });

            // Open modal for edit
            $(document).on('click', '.edit-btn', function(e){
                e.preventDefault();
                let id = $(this).data('id');
                let label = $(this).data('label');
                let url = $(this).data('url');
                let color = $(this).data('color');

                $('#form_id').val(id);
                $('#label').val(label);
                $('#url').val(url);
                $('#color').val(color);

                $('#addLinkModalLabel').text('Edit Link');
                $('#saveBtn').text('Update');
                $('#addLinkModal').modal('show');
            });

            // AJAX submit
            $('#linkForm').on('submit', function(e){
                e.preventDefault();

                let id = $('#form_id').val();
                let formData = $(this).serialize();
                let url = id ? "/form/update/" + id : "{{ route('form.store') }}";

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    success: function(response){
                        if(response.success){
                            $('#addLinkModal').modal('hide');
                            location.reload();
                        }
                    },
                    error: function(xhr){
                        alert('Something went wrong!');
                    }
                });
            });

        });
    </script>
@endsection