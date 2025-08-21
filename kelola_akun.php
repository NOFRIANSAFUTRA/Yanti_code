<?php if (isset($_SESSION['hapus_sukses'])): ?>
    <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;">
        ✅ <?php echo $_SESSION['hapus_sukses']; ?>
    </div>
    <?php unset($_SESSION['hapus_sukses']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['hapus_error'])): ?>
    <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 15px;">
        ❌ <?php echo $_SESSION['hapus_error']; ?>
    </div>
    <?php unset($_SESSION['hapus_error']); ?>
<?php endif; ?>
