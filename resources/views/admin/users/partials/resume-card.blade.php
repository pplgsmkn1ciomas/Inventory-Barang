<div class="card user-summary-card mb-3 overflow-hidden">
    <div class="card-body p-0">
        <div class="user-summary-hero p-4 p-xl-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-stretch gap-4">
                <div class="user-summary-overview flex-xl-grow-1">
                    <div class="d-inline-flex align-items-center gap-2 badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle px-3 py-2 mb-3">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Resume / Review</span>
                    </div>

                    <h5 class="fw-bold mb-2">Ringkasan data pengguna</h5>
                    <p class="user-summary-review mb-3">{{ $summary['review'] }}</p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($summary['highlights'] as $highlight)
                            <span class="badge rounded-pill border user-summary-pill">{{ $highlight }}</span>
                        @endforeach
                    </div>

                    <div class="user-summary-quick-strip">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Kelengkapan data wajah</span>
                            <span class="small fw-bold text-dark">{{ $summary['face_completion_rate'] }}%</span>
                        </div>
                        <div class="progress user-summary-progress" role="progressbar" aria-label="Kelengkapan data wajah">
                            <div class="progress-bar bg-success" style="width: {{ $summary['face_completion_rate'] }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="user-summary-ring-panel">
                    <div class="user-summary-ring" style="--user-summary-rate: {{ $summary['face_completion_rate'] }};">
                        <div class="user-summary-ring-inner">
                            <div class="user-summary-ring-value">{{ $summary['face_completion_rate'] }}%</div>
                            <div class="user-summary-ring-label">Data Wajah Siap</div>
                        </div>
                    </div>
                    <div class="user-summary-ring-caption">
                        Semakin tinggi persentasenya, semakin siap data pengguna untuk face recognition.
                    </div>
                </div>
            </div>
        </div>

        <div class="user-summary-stats px-4 pb-4">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="user-summary-stat user-summary-stat-primary h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="user-summary-stat-label">Total Pengguna</div>
                            <span class="user-summary-stat-icon">
                                <i class="fa-solid fa-users"></i>
                            </span>
                        </div>
                        <div class="user-summary-stat-value">{{ number_format($summary['total_users']) }}</div>
                        <div class="user-summary-stat-meta">Semua akun yang terdaftar.</div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="user-summary-stat user-summary-stat-info h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="user-summary-stat-label">Kategori Role</div>
                            <span class="user-summary-stat-icon">
                                <i class="fa-solid fa-layer-group"></i>
                            </span>
                        </div>

                        <div id="userSummaryRoleCarousel" class="carousel slide user-summary-carousel" data-bs-ride="carousel" data-bs-interval="3200">
                            <div class="carousel-inner">
                                @foreach($summary['role_slides'] as $roleSlide)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <div class="user-summary-slide-card">
                                            <div class="user-summary-slide-title">
                                                <i class="{{ $roleSlide['icon'] }} me-2"></i>{{ $roleSlide['label'] }}
                                            </div>
                                            <div class="user-summary-slide-value">{{ number_format($roleSlide['value']) }}</div>
                                            <div class="user-summary-slide-meta">{{ $roleSlide['meta'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($summary['role_slides']) > 1)
                                <button class="carousel-control-prev user-summary-carousel-control" type="button" data-bs-target="#userSummaryRoleCarousel" data-bs-slide="prev" aria-label="Role sebelumnya">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next user-summary-carousel-control" type="button" data-bs-target="#userSummaryRoleCarousel" data-bs-slide="next" aria-label="Role berikutnya">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="user-summary-stat user-summary-stat-success h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="user-summary-stat-label">Kelas</div>
                            <span class="user-summary-stat-icon">
                                <i class="fa-solid fa-school"></i>
                            </span>
                        </div>

                        <div id="userSummaryClassCarousel" class="carousel slide user-summary-carousel" data-bs-ride="carousel" data-bs-interval="3600">
                            <div class="carousel-inner">
                                @foreach($summary['class_slides'] as $classSlide)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <div class="user-summary-slide-card">
                                            <div class="user-summary-slide-title">Kelas {{ $classSlide['kelas'] }}</div>
                                            <div class="user-summary-slide-value">{{ number_format($classSlide['face_ready_students']) }}</div>
                                            <div class="user-summary-slide-meta">
                                                Murid dengan data face recognition tersimpan dari {{ number_format($classSlide['total_students']) }} murid ({{ $classSlide['completion_rate'] }}%).
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($summary['class_slides']) > 1)
                                <button class="carousel-control-prev user-summary-carousel-control" type="button" data-bs-target="#userSummaryClassCarousel" data-bs-slide="prev" aria-label="Kelas sebelumnya">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next user-summary-carousel-control" type="button" data-bs-target="#userSummaryClassCarousel" data-bs-slide="next" aria-label="Kelas berikutnya">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
