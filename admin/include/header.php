<link rel="stylesheet" href="../assets/css/style.css">
<!-- include/header.php -->
<header class="bg-white border-bottom py-3 px-4 sticky-top" style="z-index: 100;">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0 text-dark">
            <?php echo $page_title ?? 'Dashboard'; ?>
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