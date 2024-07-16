<?php
   $proce = new NUCLEO\RehabilitacionCardioVascular;
   $proce->setNroing($laInterc["NINORD"]);
   $proce->setConcon($laInterc["CCIORD"]);
   $datos = $proce->recuperarInformacionForm();
?>

<div class="row">
   <div class="col-sm">
      <label for="txtIngreso">Médico</label>
      <input type="text" class="form-control form-control-sm" value="<?php if($datos["codigoEstado"] != 3 ){echo $_SESSION[HCW_NAME]->oUsuario->getNombreCompleto();}else{ echo $datos['nombremedico']; } ?>" disabled>
   </div>
</div>
<input type="hidden" name="CodIngre">
<label for="txtCodigoCie" class="required">Diag. Principal</label>
<div class="row">
   <div class="col-sm">
      <input type="text" id="txtCodigoCie" name="codDiagPrin" class="form-control form-control-sm" autocomplete="off" required>
   </div>
</div>
<div class="row mt-3">
   <div class="col col-lg-2">
      <input type="text" class="form-control form-control-sm" id="loCodigoAsigna" value="<?php  if(isset($datos['CodDxPrin'])){echo $datos['CodDxPrin'];} ?>" required disabled>
   </div>
   <div class="col-sm">
      <input required type="text" class="form-control form-control-sm" id="loDescripcionAsigna" value="<?php if(isset($datos['CodDxPrinDesc'])){echo $datos['CodDxPrinDesc'];}?>" disabled>
   </div>
</div>
<div class="row">
   <div class="col-sm">
      <label for="claseDiagnostico" class="required">Tipo de Diagnostico Principal</label>
      <select class="custom-select custom-select-sm col-16" name="TipDiagPrin" id="claseDiagnostico" required>
      </select>
   </div>
</div>
<label for="txtIngreso">Diag. Relacionado</label>
<div class="row">
   <div class="col-sm">
      <input type="text" id="txtCodigoRela" class="form-control form-control-sm" >
   </div>
</div>
<div class="row mt-3">
   <div class="col col-lg-2">
      <input type="text" id="loCodigoAsignRela" name="CodDiagRela" value="<?php  if(isset($datos['CodDxRel'])){echo $datos['CodDxRel'];} ?>" class="form-control form-control-sm" disabled>
   </div>
   <div class="col-sm">
      <input type="text" id="loDescripcionAsignaRela" value="<?php if(isset($datos['CodDxRelDesc'])){echo $datos['CodDxRelDesc'];} ?>" class="form-control form-control-sm" disabled>
   </div>
</div>
<div class="row">
   <div class="col-sm">
      <label for="txtIngreso" class="required">Finalidad</label>
      <select class="custom-select custom-select-sm col-16" name="CodFinalidad"  required id="selFinalidad" required>
         <option value=""></option>
      </select>
   </div>
</div>

<div class="row">
   <div class="col-sm">
      <label for="txtIngreso">Resultados</label>
      <textarea class="form-control form-control-sm" name="ResultExa" id="ResultExa" cols="30" rows="10"><?php if(isset($datos['Descripcion'])){echo $datos['Descripcion'];}?></textarea>
   </div>
</div>