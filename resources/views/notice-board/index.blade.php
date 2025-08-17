<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.jpg">
    <link rel="icon" href="images/logo.png" type="image/gif" sizes="16x16">

    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup/dist/magnific-popup.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Raleway:wght@500&display=swap" rel="stylesheet">

    <!-- jQuery & JS Files -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup/dist/jquery.magnific-popup.min.js"></script>

    <style>
        .bg-danger {
            background-color: #dc3545 !important;
        }

        .underline-para {
            text-decoration: underline;
            font-weight: bold;
        }

        .shadow-para {
            height: 3px;
            background: rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.4);
        }

        table {
            border: 1px solid black;
        }

        th,
        td {
            border: 1px solid black !important;
        }

        .btn-popup {
            text-align: right;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: none;"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Custom form</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container mt-5">
                        <form action="{{ route('notices.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" name="date" required>
                            </div>

                            <div class="mb-3">
                                <label for="description1" class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="link" class="form-label">Link</label>
                                <input type="text" class="form-control" name="link" placeholder="https://example.com">
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Upload Image</label>
                                <input class="form-control" type="file" name="image[]" accept=".jpg, .jpeg, .png, .pdf"
                                    multiple>
                            </div>

                            <div class="mb-3">
                                <label for="description2" class="form-label">Remarks</label>
                                <textarea class="form-control" name="remark" rows="3"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="row m-5">
        <div class="col-md-12 p-0 p-md-5 h5">
            <p class="underline-para">地產代理監管局 - 信息通知</p>
            <p>致：各地產代理及營業員, 本公司根據地產代理監管局要求, 不定時向從業員及地產代理發出執業指引及有關地產代理信息，請各代理務必閱讀信息及遵從相關條例。</p>
            <p>為求與時並進, 本公司亦不時發出行業信息, 以供大家閱覽, 希望大家也積極裝備自己, 以應對行業日新月異之變化。</p>
            <p class="mt-5">保誠物業代理</p>
        </div>
    </div>

    <div class="shadow-lg shadow-para">
        <hr class="shadow-lg bg-secondary">
    </div>

    <!-- Table Section -->
    <div class="row m-md-5 m-1 p-md-5 py-1 px-3" style="overflow-x: auto">
        <div class="btn-popup">
            <a href="{{ url('/admin-search') }}" class="btn btn-danger">Back</a>
            @if(auth()->user()->role == 'admin')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Add</button>
            @endif
        </div>

        <table class="table p-5">
            <thead>
                <tr>
                    <th>日期 Date</th>
                    <th>內容 Descriptions</th>
                    <th>連結 Detailed links</th>
                    <th>檔案 Files</th>
                    <th>備註 Remarks</th>
                    @if(auth()->user()->role == 'admin')
                    <th>Actiion</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($notices as $notice)
                    <tr>
                        <td>
                            <div style="width: 100px;">{{ $notice->date }}</div>
                        </td>
                        <td>
                            <div style="width: 200px;">{{ $notice->description }}</div>
                        </td>
                        <td>
                            @if($notice->link)
                                <a href="{{ $notice->link }}" target="_blank">Link</a>
                            @endif
                        </td>
                        <td>
                            @if($notice->files)
                                <div class="row mx-1 popup-gallery" style="width:200px">
                                    @foreach(explode(',', $notice->files) as $file)
                                        <a href="{{ asset($file) }}" class="col-md-4 col-6 px-0" title="Click to enlarge">
                                            <img class="mb-1 border p-1 img-fluid" src="{{ asset($file) }}" data-file='{{ asset($file) }}' alt="Uploaded Image">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="width: 100px;">{{ $notice->remark }}</div>
                        </td>
                        @if(auth()->user()->role == 'admin')
                            <td>
                                <form action="{{ route('notices.destroy', $notice->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="m-5 p-5"></div>

    <!-- Script -->
    <script>
        $(document).ready(function () {
            $('.popup-gallery').magnificPopup({
                delegate: 'a',
                type: 'image',
                tLoading: 'Loading image #%curr%...',
                mainClass: 'mfp-img-mobile',
                gallery: {
                    enabled: true,
                    navigateByImgClick: true,
                    preload: [0, 1]
                },
                image: {
                    tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                    titleSrc: function (item) {
                        return item.el.attr('title');
                    }
                }
            });
        });
    </script>
</body>

</html>