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
                @foreach($summary['stats'] as $stat)
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="user-summary-stat user-summary-stat-{{ $stat['tone'] }} h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="user-summary-stat-label">{{ $stat['label'] }}</div>
                                <span class="user-summary-stat-icon">
                                    <i class="{{ $stat['icon'] }}"></i>
                                </span>
                            </div>
                            <div class="user-summary-stat-value">{{ number_format($stat['value']) }}</div>
                            <div class="user-summary-stat-meta">{{ $stat['meta'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
