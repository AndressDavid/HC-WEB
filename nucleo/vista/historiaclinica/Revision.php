<div class="card card-block">
	<div class="card-header" id="headerRevisionS">
		<a href="#Revis_uno" class="card-link text-dark"><b>Revisión de Sistema</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormRevision" name="FormRevision" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

<?php
		$lcPrefijo='sis';
		$laLista=[
			'Visual'			=>['Visual'				,'eye'],
			'Otorrino'			=>['Otorrino'			,'assistive-listening-systems'],
			'Pulmonar'			=>['Pulmonar'			,'lungs'],
			'Cardiovascular'	=>['Cardiovascular'		,'heartbeat'],
			'Gastrointestinal'	=>['Gastrointestinal'	,'file-alt'],
			'Genitourinario'	=>['Genitourinario'		,'file-alt'],
			'Endocrino'			=>['Endocrino'			,'burn'],
			'Hematologico'		=>['Hematológico'		,'tint'],
			'Dermatologico'		=>['Dermatológico'		,'viruses'],
			'Oseo'				=>['Osteomuscular'		,'hiking'],
			'Nervioso'			=>['Nervioso'			,'male'],
			'Siquico'			=>['Síquico'			,'brain'],
		];
		foreach($laLista as $lcNombre=>$laItem){
			$lcIdLabel='lbl'.$lcNombre;
			$lcIdControl=$lcPrefijo.$lcNombre; ?>
			<div class="col-12 pb-4">
				<label id="<?php echo $lcIdLabel; ?>" for="<?php echo $lcIdControl; ?>"><?php echo $laItem[0]; ?></label>
				<div class="input-group">
				  <div class="input-group-prepend">
					<span class="input-group-text"><i class="fas fa-<?php echo $laItem[1]; ?>"></i></span>
				  </div>
				  <textarea name="<?php echo $lcIdControl; ?>" id="<?php echo $lcIdControl; ?>" type="text" class="form-control <?= $lcCopyPaste ?>"></textarea>
				</div>
			</div>
<?php	} ?>

		</form>
	</div>
</div>

<script type="text/javascript" src="vista-historiaclinica/js/Revision.js"></script>