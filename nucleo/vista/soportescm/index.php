<?php

$laPrmPags = include( __DIR__ . '/paginas.php');

if (isset($_GET['p'])) {

	$lcOpcion = $_GET['p'];
	$laOpcionesValidas = array_keys($laPrmPags['paginas']);

	if (in_array($lcOpcion, $laOpcionesValidas)) {
		$lcPagina = __DIR__ .'/'.trim(strtolower($lcOpcion)).".php";
		include($lcPagina);
	}

} else {

?>

	<div class="container-fluid">
		<div id="divCard" class="card mt-3">

			<div class="card-header">
				<div class="media">
					<i class="fas fa-notes-medical align-self-center mr-3"></i>
					<div class="media-body"><h5>Opciones Radicación</h5></div>
				</div>
			</div>

			<div id="divLstOpciones" class="card-body">
				<div class="list-group">

					<?php foreach ($laPrmPags['paginas'] as $lcPagina => $laPagina) : ?>

						<a href="modulo-soportescm&amp;p=<?= $lcPagina ?>" class="list-group-item list-group-item-action border-0">
							<div class="media">
								<i class="<?= $laPagina['icono'] ?> align-self-center mr-3"></i>
								<div class="media-body">
									<b><?= $laPagina['titulo'] ?></b><br><?= $laPagina['descrp'] ?>
								</div>
							</div>
						</a>

					<?php endforeach; ?>

				</div>
			</div>
		</div>
	</div>

<?php
}
?>
