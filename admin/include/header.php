<!-- include/header.php -->
<header class="top-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fas fa-campground me-2 text-maroon"></i>
            <?php echo $page_title ?? 'Halaman Admin'; ?>
        </h4>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-sm-block">
                <div class="fw-bold mb-0">Admin SIMRO</div>
                <small class="text-muted">Administrator</small>
            </div>
            <div class="bg-maroon text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>
</header>