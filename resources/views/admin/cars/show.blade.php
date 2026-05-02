@extends('layouts.admin')

@section('title', $car->title)

@section('content')

    <style>
        .wrap {
            max-width: 1200px;
            margin: auto;
            color: #e5e7eb;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 30px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .section {
            margin-bottom: 30px;
        }

        .title {
            font-size: 26px;
            font-weight: 700;
        }

        .price {
            font-size: 22px;
            color: #ef4444;
            font-weight: bold;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-new {
            background: #16a34a;
        }

        .badge-old {
            background: #f59e0b;
            color: #000;
        }

        /* ẢNH CHÍNH */
        .main-img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
        }

        /* GALLERY */
        .gallery {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .gallery img {
            width: 100%;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
        }

        /* MODAL ZOOM */
        .modal-img {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-img img {
            max-width: 90%;
            max-height: 90%;
        }

        /* SPEC */
        .specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 15px;
        }

        .spec {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 8px;
        }

        .label {
            font-size: 12px;
            color: #9ca3af;
        }

        .value {
            font-weight: 600;
            color: #fff;
        }

        /* VIDEO */
        video,
        iframe {
            width: 100%;
            max-height: 300px;
            border-radius: 10px;
        }

        /* ACTION */
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-edit {
            background: #facc15;
            color: #000;
        }

        .btn-delete {
            background: #ef4444;
            color: #fff;
        }
    </style>

    <div class="wrap">

        <div class="grid">

            {{-- LEFT --}}
            <div>

                {{-- ẢNH --}}
                <div class="section">
                    @if ($car->image)
                        <img src="{{ asset('storage/' . $car->image) }}" class="main-img" onclick="zoom(this.src)">
                    @endif

                    {{-- GALLERY --}}
                    @if ($car->gallery)
                        <div class="gallery">
                            @foreach ($car->gallery as $img)
                                <img src="{{ asset('storage/' . $img) }}" onclick="zoom(this.src)">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- VIDEO --}}
                @if ($car->video_file || $car->video_url)
                    <div class="section">
                        <h3>Video</h3>

                        @if ($car->video_file)
                            <video controls>
                                <source src="{{ asset('storage/' . $car->video_file) }}">
                            </video>
                        @elseif($car->video_url)
                            @php
                                preg_match('/v=([^&]+)/', $car->video_url, $m);
                                $id = $m[1] ?? null;
                            @endphp

                            @if ($id)
                                <iframe src="https://www.youtube.com/embed/{{ $id }}"></iframe>
                            @endif
                        @endif
                    </div>
                @endif

            </div>

            {{-- RIGHT --}}
            <div>

                <div class="section">
                    <div class="title">{{ $car->title }}</div>

                    <div class="price">
                        {{ number_format($car->price) }} VNĐ
                    </div>

                    <div>
                        @if ($car->status == 1)
                            <span class="badge badge-new">Xe mới</span>
                        @else
                            <span class="badge badge-old">Xe cũ</span>
                        @endif
                    </div>
                </div>

                {{-- SPECS --}}
                <div class="section specs">

                    <div class="spec">
                        <div class="label">Hãng</div>
                        <div class="value">{{ $car->brand->name ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Năm</div>
                        <div class="value">{{ $car->year ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Km</div>
                        <div class="value">{{ $car->mileage_km ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Nhiên liệu</div>
                        <div class="value">{{ $car->fuel ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Hộp số</div>
                        <div class="value">{{ $car->transmission ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Màu</div>
                        <div class="value">{{ $car->color ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Nội thất</div>
                        <div class="value">{{ $car->interior_color ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Động cơ</div>
                        <div class="value">{{ $car->engine ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Xuất xứ</div>
                        <div class="value">{{ $car->origin ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Kiểu dáng</div>
                        <div class="value">{{ $car->body_type ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Dẫn động</div>
                        <div class="value">{{ $car->drive_type ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Số chỗ</div>
                        <div class="value">{{ $car->seats ?? '---' }}</div>
                    </div>
                    <div class="spec">
                        <div class="label">Số cửa</div>
                        <div class="value">{{ $car->doors ?? '---' }}</div>
                    </div>

                </div>

                {{-- DESCRIPTION --}}
                @if ($car->description)
                    <div class="section">
                        <h3>Mô tả</h3>
                        <p>{!! nl2br(e($car->description)) !!}</p>
                    </div>
                @endif

                {{-- ACTION --}}
                <div class="actions">
                    <a href="{{ route('admin.cars.edit', $car->car_id) }}" class="btn btn-edit">Sửa</a>

                    <form action="{{ route('admin.cars.destroy', $car->car_id) }}" method="POST"
                        onsubmit="return confirm('Xóa xe này?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-delete">Xóa</button>
                    </form>
                </div>

            </div>

        </div>

    </div>

    {{-- MODAL --}}
    <div id="imgModal" class="modal-img" onclick="this.style.display='none'">
        <img id="modalImg">
    </div>

    <script>
        function zoom(src) {
            document.getElementById('imgModal').style.display = 'flex';
            document.getElementById('modalImg').src = src;
        }
    </script>

@endsection
