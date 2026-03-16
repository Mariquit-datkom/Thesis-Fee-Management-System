<div class="header-container">
    <div class="left-header">
        <img src="assets/images/school_logo.png" alt="school-logo" class="school-logo">
        <div class="nav-item-container">
            <?php
                $navItems = [
                    'payment.php' => ['text' => 'Payment'],
                    'studentRecords.php' => ['text' => 'Student Records']
                ];

                foreach ($navItems as $page => $details):
                    $isActive = ($currentPage === $page);
                    $href = $isActive ? 'javascript:void(0)' : $page;
                    $activeClass = $isActive ? 'active' : '';
            ?>
            <div class="nav-item">
                <a href="<?php echo $href ?>" class="<?php echo $activeClass ?>"><?php echo $details['text'] ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="right-header">
        
    </div>
</div>