<!-- Footer -->
<footer class="bg-dark text-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="bi bi-shop"></i> Mi Shopping</h5>
                <p class="mb-0">Tu plataforma de compras y promociones favorita</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0">&copy; <?= date('Y') ?> Mi Shopping. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<?php if (isset($custom_js)): ?>
    <script src="js/<?= $custom_js ?>"></script>
<?php endif; ?>
</body>
</html>
