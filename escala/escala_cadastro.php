<?php
include "../template/header.php";
include "escala_bd.php";
include "../pessoa/pessoa_bd.php";
include "../grupo/grupo_bd.php";

excluir ();
incluir ();
monta_html_form_cadastro ();
listar ();

?>

<?php
function monta_html_form_cadastro() {
	echo "<h2>Período da escala</h2>";
	echo "<form action = 'escala_cadastro.php' method = 'post'>";
	echo "<div id='tab1' class='tabcontent' style='display: block;'>";
	echo "<div class='form'>";
	
	echo "<div class='form_row'>";
	echo "<label>Data inicial:</label> <input type='text' name = 'data_inicio' class='form_input' >";
	echo "</div>";
	echo "<div class='form_row'>";
	echo "<label>Data final:</label> <input type='text' name = 'data_fim' class='form_input' >";
	echo "</div>";
	echo "<div class='form_row'>";
	echo "<label>Descrição:</label> <input type='text' name = 'descricao' class='form_input' >";
	echo "</div>";
	echo "<input type='hidden' name='incluir' value='1' />";
	monta_html_select_grupos ();
	echo "<div class='form_row'>";
	echo "<input type='submit' class='form_submit' value='+ Nova escala' title = 'clique para criar uma nova escala'>";
	echo "</div>";
	echo "<div class='clear'></div>";
	echo "</div>";
	echo "</div>";
	echo "</form>";
}
function excluir() {
	if (! empty ( $_POST ["excluir"] )) {
		
		if (! excluir_escala ( $_POST ["id"] )) {
			echo "<span class = 'notification n-error'>Não foi possível remover a escala</span>";
		} else {
			echo "<span class = 'notification n-success'>Escala removida.</span>";
		}
	}
}
function incluir() {
	if (! empty ( $_POST ["incluir"] ) | ! empty ( $_POST ["editar"] )) {
		
		$escala ["data_inicio"] = $_POST ["data_inicio"];
		$escala ["data_fim"] = $_POST ["data_fim"];
		$escala ["grupo_id"] = $_POST ["grupo_id"];
		$escala ["descricao"] = $_POST ["descricao"];
		
		if (empty ( $escala ["data_inicio"] ) | empty ( $escala ["data_fim"] ) | empty ( $escala ["grupo_id"] ) | empty ( $escala ["descricao"] )) {
			
			echo "<span class = 'notification n-error'>Para inserir uma nova escala, é preciso inserir uma data de início, uma data fim, uma descrição  e um grupo.</span>";
		} else {
			
			$data_inicio = explode ( "/", $escala ["data_inicio"] );
			$data_fim = explode ( "/", $escala ["data_fim"] );
			
			if ($data_inicio [0] > $data_fim [0]) {
				
				echo "<span class = 'notification n-error'>É preciso que a data final seja maior que a data inicial.</span>";
			} else if ($data_inicio [1] != $data_fim [1]) {
				echo "<span class = 'notification n-error'>O período precisa estar dentro do mesmo mês.</span>";
			} else if ($data_inicio [2] != $data_fim [2]) {
				echo "<span class = 'notification n-error'>O período precisa estar dentro do mesmo ano.</span>";
			} else {
				
				$escala ["data_inicio"] = $data_inicio [2] . "-" . $data_inicio [1] . "-" . $data_inicio [0];
				$escala ["data_fim"] = $data_fim [2] . "-" . $data_fim [1] . "-" . $data_fim [0];
				
				$resultado = "";
				if (! empty ( $_POST ["incluir"] )) {
					$resultado = inserir_escala ( $escala );
				} else if (! empty ( $_POST ["editar"] )) {
					$escala["id"] = $_POST["escala_id"];
					
					if (count(get_first_escala_pessoa($escala["id"])) > 0) {
						echo "<span class = 'notification n-error'>Não é possível alterar essa escala, somente a descrição, pois existem pessoas associadas.</span>";
						$escala = get_escala_por_id($_POST["escala_id"]);
						$escala["descricao"] = $_POST["descricao"];
						$resultado = alterar_escala ( $escala );
					}
					else if (count(get_first_escala_jornada($escala["id"])) > 0) {
						echo "<span class = 'notification n-error'>Não é possível alterar essa escala, somente a descrição, pois existem jornadas associadas.</span>";
						$escala = get_escala_por_id($_POST["escala_id"]);
						$escala["descricao"] = $_POST["descricao"];
						$resultado = alterar_escala ( $escala );
					}
					else
						$resultado = alterar_escala ( $escala );
				}
				
				if ($resultado)
					echo "<span class = 'notification n-success'>Escala salva.</span>";
				else
					echo "<span class = 'notification n-error'>Erro ao salvar a escala.</span>";
			}
		}
	}
}
function monta_html_select_grupos() {
	$grupos = get_grupos ();
	
	echo "<div class='form_row'><label>Grupo:</label><select class = 'form_select' name = 'grupo_id'>";
	
	echo "<option value = ''></option>";
	for($i = 0; $i < count ( $grupos ); ++ $i) {
		
		echo "<option value = '" . $grupos [$i] ["id"] . "'>" . $grupos [$i] ["nome"] . "</option>";
	}
	echo "</select></div>";
}
function listar() {
	$escalas = get_escalas ();
	
	if (count ( $escalas ) > 0) {
		echo "<h2>Escalas - para definir pessoas e horários para a escala clique no ícone do calendário</h2>";
		echo "<table id='rounded-corner'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Período</th>";
		echo "<th>Descrição</th>";
		echo "<th>Grupo</th>";
		echo "<th></th>";
		echo "<th></th>";
		echo "<th></th>";
		echo "<th></th>";
		echo "<th></th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tfoot>";
		echo "<tr>";
		echo "<td colspan='12'></td>";
		echo "</tr>";
		echo "</tfoot>";
		echo "<tbody>";
		
		$cont = 0;
		foreach ( $escalas as $escala ) {
			
			if ($cont ++ % 2 == 0)
				echo "<tr class='odd'>";
			else
				echo "<tr class='even'>";
			echo "<td>" . get_data ( $escala ["data_inicio"] ) . " - " . get_data ( $escala ["data_fim"] ) . "</td>";
			echo "<td>" . $escala ["descricao"] . "</td>";
			echo "<td>" . get_grupo_por_id ( $escala ["grupo_id"] ) ["nome"] . "</td>";
			echo "<td>";
			echo "<form action = 'escala_montagem.php' method = 'post'>";
			echo "<input type='hidden' name='escala_id' value='" . $escala ["id"] . "' />";
			echo "<input type='submit' value = '' style = 'background-image:url(../resources/img/calendar.png);repeat-x:no-repeat;width:32px;height:32px;cursor:pointer;' title = 'clique para montar a escala'/>";
			echo "</form>";
			echo "</td>";
			echo "<td>";
			echo "<td>";
			echo "<form action = 'escala_edicao.php' method = 'post'>";
			echo "<input type='hidden' name='editar' value='1' />";
			echo "<input type='hidden' name='escala_id' value='" . $escala ["id"] . "' />";
			echo "<input type='submit' value = '' style = 'background-image:url(../resources/img/edit.png);repeat-x:no-repeat;width:20px;height:20px;cursor:pointer;' title = 'clique para montar a escala'/>";
			echo "</form>";
			echo "</td>";
			echo "<td>";
			echo "<form method = 'post'>";
			echo "<input type='hidden' name='excluir' value='1' />";
			echo "<input type='hidden' name='id' value='" . $escala ["id"] . "' />";
			echo "<input type='submit' value = '' style = 'background-image:url(../resources/img/trash.gif);repeat-x:no-repeat;width:20px;cursor:pointer;' title = 'clique para excluir'/>";
			echo "</form>";
			echo "</td>";
			echo "<td>";
			echo "<form action = 'escala_relatorio.php' method = 'post'>";
			echo "<input type='hidden' name='escala_id' value='" . $escala ["id"] . "' />";
			echo "<input type='submit' value = '' style = 'background-image:url(../resources/img/report.png);repeat-x:no-repeat;width:36px;height:36px;cursor:pointer;' title = 'clique para visualizar a escala'/>";
			echo "</form>";
			echo "</td>";
			
			echo "</tr>";
		}
		
		echo "</tbody></table>";
	}
}

include "../template/footer.php"?>


