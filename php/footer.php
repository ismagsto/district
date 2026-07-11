</main>

<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="h4 fw-black">district<span class="accent-dot">.</span></h2>
                <p class="muted-text">Moda urbana contemporánea diseñada para destacar.</p>
            </div>

            <div class="col-md-2">
                <h3 class="h6 text-uppercase">Tienda</h3>
                <ul class="footer-list">
                    <li><a href="<?= url('catalogo.php') ?>">Catálogo</a></li>
                    <li><a href="<?= url('favoritos.php') ?>">Favoritos</a></li>
                    <li><a href="<?= url('carrito.php') ?>">Carrito</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h3 class="h6 text-uppercase">Nuestra esencia</h3>

                <ul class="footer-list">
                    <li>Materiales de calidad</li>
                    <li>Producción responsable</li>
                    <li>Diseño detallado</li>
                </ul>
            </div>

            <div class="col-md-3">
                <h3 class="h6 text-uppercase">Recibir novedades</h3>
                <form class="d-flex gap-2" onsubmit="event.preventDefault(); showToast('Gracias por suscribirte', 'success');">
                    <input class="form-control" type="email" placeholder="tu@email.com" aria-label="Email newsletter" required>
                    <button class="btn btn-accent" type="submit">OK</button>
                </form>
            </div>
        </div>

        <hr class="footer-line">

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 small muted-text">
            <span>&copy; 2026.</span>
            <span>Programado y diseñado por Ismael Gesto Herrera</span>
        </div>
    </div>
</footer>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastArea"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= url('js/app.js') ?>"></script>
</body>
</html>
