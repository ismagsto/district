<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../php/config.php';
}

/* FUNCIÓN PARA NORMALIZAR TEXTO */

if (!function_exists('catalogNormalizeText')) {
    function catalogNormalizeText(string $text): string
    {
        $text = function_exists('mb_strtolower')
            ? mb_strtolower(trim($text), 'UTF-8')
            : strtolower(trim($text));

        $search = ['á', 'à', 'ä', 'â', 'ã', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î', 'ó', 'ò', 'ö', 'ô', 'õ', 'ú', 'ù', 'ü', 'û', 'ñ', 'ç'];
        $replace = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c'];

        $text = str_replace($search, $replace, $text);
        $text = preg_replace('/[^a-z0-9\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}

/* LECTURA Y LIMPIEZA DE FILTROS */

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['categoria'] ?? '');
$order = trim($_GET['orden'] ?? 'recientes');

$where = [
    'p.activo = 1',
    'c.activa = 1'
];

$params = [];

/* FILTRO DE CATEGORÍA */

if ($category !== '') {
    $where[] = 'c.slug = :categoria';
    $params[':categoria'] = $category;
}


$searchSql = "LOWER(
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        CONCAT_WS(' ', p.nombre, p.descripcion, c.nombre),
        'á','a'), 'é','e'), 'í','i'), 'ó','o'), 'ú','u'),
        'Á','a'), 'É','e'), 'Í','i'), 'Ó','o'), 'Ú','u'),
        'ñ','n'), 'Ñ','n')
)";

$normalizedQ = catalogNormalizeText($q);

if ($normalizedQ !== '') {
    $where[] = $searchSql . ' LIKE :q';
    $params[':q'] = '%' . $normalizedQ . '%';
}

/* ORDENACIÓN */

switch ($order) {
    case 'antiguos':
        $orderBy = 'p.creado_en ASC, p.id ASC';
        break;

    case 'precio_asc':
        $orderBy = 'p.precio ASC, p.nombre ASC';
        break;

    case 'precio_desc':
        $orderBy = 'p.precio DESC, p.nombre ASC';
        break;

    case 'nombre_asc':
        $orderBy = 'p.nombre ASC';
        break;

    case 'nombre_desc':
        $orderBy = 'p.nombre DESC';
        break;

    case 'recientes':
    default:
        $orderBy = 'p.creado_en DESC, p.id DESC';
        break;
}

/* FUNCIÓN DE CONSULTA */

$baseSelect = '
    SELECT
        p.id,
        p.categoria_id,
        p.nombre,
        p.slug,
        p.descripcion,
        p.precio,
        p.precio_anterior,
        p.imagen_principal,
        p.destacado,
        p.activo,
        p.creado_en,
        c.nombre AS categoria_nombre,
        c.slug AS categoria_slug,
        COALESCE(st.stock_total, 0) AS stock_total
    FROM productos p
    INNER JOIN categorias c
        ON c.id = p.categoria_id
    LEFT JOIN (
        SELECT
            producto_id,
            SUM(unidades) AS stock_total
        FROM stock
        GROUP BY producto_id
    ) st
        ON st.producto_id = p.id
';

$sql = $baseSelect . '
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

/* BÚSQUEDA APROXIMADA SI LIKE NO ENCUENTRA NADA */

if (empty($products) && $normalizedQ !== '') {
    $fallbackWhere = [
        'p.activo = 1',
        'c.activa = 1'
    ];

    $fallbackParams = [];

    if ($category !== '') {
        $fallbackWhere[] = 'c.slug = :categoria';
        $fallbackParams[':categoria'] = $category;
    }

    $fallbackSql = $baseSelect . '
        WHERE ' . implode(' AND ', $fallbackWhere) . '
        ORDER BY ' . $orderBy;

    $fallbackStmt = $pdo->prepare($fallbackSql);
    $fallbackStmt->execute($fallbackParams);
    $candidateProducts = $fallbackStmt->fetchAll();

    $queryWords = array_filter(explode(' ', $normalizedQ));

    foreach ($candidateProducts as $candidate) {
        $haystack = catalogNormalizeText(
            $candidate['nombre'] . ' ' .
            ($candidate['descripcion'] ?? '') . ' ' .
            ($candidate['categoria_nombre'] ?? '')
        );

        $haystackWords = array_filter(explode(' ', $haystack));
        $matched = false;

        foreach ($queryWords as $queryWord) {
            foreach ($haystackWords as $haystackWord) {
                if (
                    strpos($haystackWord, $queryWord) !== false ||
                    strpos($queryWord, $haystackWord) !== false ||
                    levenshtein($queryWord, $haystackWord) <= 2
                ) {
                    $matched = true;
                    break 2;
                }
            }
        }

        if ($matched) {
            $products[] = $candidate;
        }
    }
}

/* SALIDA HTML  */

if (empty($products)): ?>
    <div class="col-12">
        <div class="surface-card p-5 text-center">
            <h2 class="h4 fw-bold">No hay productos</h2>

            <p class="muted-text mb-3">
                No se han encontrado productos con esos filtros.
            </p>

            <a class="btn btn-outline-light" href="<?= url('catalogo.php' . ($category !== '' ? '?categoria=' . urlencode($category) : '')) ?>">
                Limpiar búsqueda
            </a>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($products as $product): ?>
    <div class="col-sm-6 col-lg-4">
        <?php require __DIR__ . '/../php/product-card.php'; ?>
    </div>
<?php endforeach; ?>
