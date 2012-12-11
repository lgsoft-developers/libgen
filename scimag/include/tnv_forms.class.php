<?php
/*
Версия 1.16

1.15 - для поля checkbox добавлен атрибут is_unique. Если он = 1 и для данной записи выставляется единица, то перед этим у всех записей данное поле обнулсяется. Обычное применние - это флаг для списка. обозначающий одну из записей как дефолтную.
1.16
 + Для image добавлено поле resize=2 , при выставлении которого кроме уменьшеного изображения делается еще и среднее..
*/
class tnv_form
{
    var $version = '0.3';
    var $formTemolate = '';
    var $fields = array();
    var $types = array('edit', 'hidden', 'spr_select', 'textarea', 'tree_select',
        'callendar', 'is_key', 'sql_select', 'fckeditor', 'image', 'checkbox');
    var $dbTableData = 'tnv_posts';
    var $dbLink = '';
    var $sess = array();
    var $mod_path_ = '';
    var $spr = 0;
    var $val;
    var $sql;
    var $sqlError;
    var $error;
    var $category_id;
    var $user_id = 0;
    var $rowTemplate;
    var $showTemplate;
    var $mode = 'admin';
    var $tr;
    var $tid = -1;
    var $fck = array();
    var $img;
    var $dbTableImages;
    var $image_path;

    function tnv_form($pars)
    {
        global $HTTP_SERVER_VARS;
        $this->dbLink = $pars['dblink'];
        $this->parent_url = $pars['parent_url'];
        $this->sess = $pars['sessions'];
        //include($this->sess['site_path']."/".$this->sess['admin_modules_path']."/".$this->sess['m']."/tnv_sprs.class.php");

        //$this->spr = new tnv_sprs($this->dbLink,$this->sess['site_path'],$this->sess['modules_path']."/".$this->sess['m']);
        $this->spr = new tnv_sprs($this->dbLink, '', '');
        $this->spr->dbTableData = $pars['dbSprTable'];

        //include($this->sess['site_path']."/common/validator.class.php");
        $this->val = new validate();
        //include($this->sess['site_path']."/".$this->sess['admin_modules_path']."/".$this->sess['m']."/tnv_tree.class.php");
        $this->tr = new tnv_tree(array('dbTableData' => '', 'dbLink' => $this->dbLink,
            'parent_url' => ''));
        unset($this->user_id);

        $this->img = new image_storage($this->dbLink, $HTTP_SERVER_VARS['DOCUMENT_ROOT']);
        $this->dbTableImages=$this->img->dbTable = $pars['dbImgTable'];
        $this->img->image_path = $pars['ImagePath'];
    }

    function dbQuery($sql)
    {
        $this->sql = $sql;
        //echo "$sql<br>";
        $res = mysql_query($sql, $this->dbLink);
        if (!$res) {
            $this->sqlError = mysql_error();
        }
        return $res;
    }

    function dbFetchArray($res)
    {
        $row = mysql_fetch_array($res);
        return $row;
    }

    function InsertedId()
    {
        return mysql_insert_id($this->dbLink);
    }

    function dbNumRows($res)
    {
        return mysql_num_rows($res);
    }

    function showError()
    {
        $str = '<div style=" border : thin red;backgroud : #EEEEEE">SQL: ' . $this->sql .
            '<br>sqlError: ' . $this->sqlError . '<br>Error: ' . $this->error . ' </div>';
        echo $str;
    }

    function addParam($param)
    {
        $i = array_push($this->fields, $param);
        //echo "elements $i<br>";
    }

    function checkInputData(&$values)
    {
        $errors = '';
        $fields = $this->fields;
        while ($field = array_shift($fields)) {
            if ((isset($field['f_name'])) && (!isset($field['is_key']))) {
                //echo "checking ".$field['f_name']." ".$field['type']." ".$values[$field['f_name']]."<br>";
                #Проверяем все ли значения полей присутсвуют
                if (isset($values[$field['f_name']])) {
                    #Если поле обязательно, то проверяем на заполненность
                    if (isset($field['require'])) {

                        if ($field['type'] == 'edit' || $field['type'] == 'textarea' || $field['type'] ==
                            'fckeditor') {
                            //echo $field['f_name']." = [".$values[$field['f_name']]."]<br>";
                            if ($values[$field['f_name']] == '')
                                $errors .= "Поле \"" . $field['title'] . "\" должно быть заполнено<br>";
                        }

                        if ($field['type'] == 'spr_select') {
                            if ($values[$field['f_name']] == 0)
                                $errors .= "Выберите одно из значений из списка \"" . $field['title'] . "\"<br>";
                        }

                    }

                    #Если для поля установлена функция для проверки - проверяем
                    if (isset($field['check_func'])) {

                        if ($field['check_func'] == 'number') {
                            if (!$this->val->is_digit($values[$field['f_name']]))
                                $errors .= "Поле \"" . $field['title'] . "\" должно быть числом<br>";
                        }

                        if ($field['check_func'] == 'email') {
                            if (!$this->val->is_email($values[$field['f_name']]))
                                $errors .= "Поле \"" . $field['title'] . "\" не является правильным адресом электронной почты<br>";
                        }

                    }

                } else {
                    if ($field['type'] != 'image' && $field['type'] != 'checkbox')
                        $errors .= "Отсутствуют данные по полю \"" . $field['title'] . "\"<br>";
                }
            }
        }
        return $errors;
    }

    function getIdFieldName()
    {
        $fields = $this->fields;
        while ($field = array_shift($fields)) {
            if (isset($field['is_key']))
                return $field['dbfield'];
        }
    }

    function showForm($action, $values, $errors = '')
    {
        //$spr = new tnv_sprs($this->dbLink,$this->sess['site_path'],$this->sess['modules_path']."/".$this->sess['m']);
        $fields = $this->fields;
        $element = '';
        if ($action == 'edit') {
            $tid = $this->getIdFieldName();
            $sql = "select * from $this->dbTableData where $tid = " . $values['id'];
            $res = $this->dbQuery($sql);
            if (!$res)
                return false;
            $values = $this->dbFetchArray($res);
            //print_r($values);
        }
        if ($action == 'update')
            $action = 'edit';


        //$out = "<form name=defraz33 action='%PARENT_URL%' method=POST>";
        $out = $this->formTemplate;
        //echo count($this->fields);
        //for($i=0;$i<=count($this->fields);$i++){
        while ($field = array_shift($fields)) {
            //echo count($this->fields);
            //echo $field['type']."<br>";
            $element = "";
            if ($field['type'] == 'is_key' && $action != 'add') {
                //if (isset($values[$field['f_name']])) $element="<input type=hidden name=".$field['f_name']." value=".$values[$field['f_name']].">";
                //else
                if (isset($values[$field['dbfield']]))
                    $value = $values[$field['dbfield']];
                else
                    $value = $values[$field['f_name']];
                $element = "<input type=hidden name=" . $field['f_name'] . " value=" . $value .
                    ">";
                $id = $value;
            }

            if ($field['type'] == 'edit') {
                $element = "<input type=edit name='" . $field['f_name'] . "'";
                if (isset($field['size']))
                    $element .= " size=" . $field['size'] . " ";
                if (isset($values[$field['f_name']]))
                    $element .= " value='" . stripslashes($values[$field['f_name']]) . "' ";
                else
                    $element .= " value='' ";
                if (isset($field['style'])) $element .= " ".$field['style']." ";
                $element .= " >";
            }

            if ($field['type'] == 'spr_select') {
                if (isset($values[$field['f_name']]))
                    $val = $values[$field['f_name']];
                else
                    $val = -1;
                $element = $this->spr->getSelect($field['f_name'], $field['spr_id'], $val, $field['spr_def_value']);
                if (!$element)
                    $this->spr->showError();
            }

            if ($field['type'] == 'textarea') {
                $element = "<textarea name='" . $field['f_name'] . "' ";
                if (isset($field['style'])) $element .= " ".$field['style']." ";
                if (isset($field['cols']))
                    $element .= " cols=" . $field['cols'] . " ";
                if (isset($field['rows']))
                    $element .= " rows=" . $field['rows'] . " ";
                if (isset($values[$field['f_name']]))
                    $element .= " >" . $values[$field['f_name']] . "</textarea> ";
                else
                    $element .= " ></textarea> ";
            }

            if ($field['type'] == 'submit') {
                $element = "<input type=submit value='" . $field['value'] . "' >";
            }

            if ($field['type'] == 'button') {
                $element = "<input type=button value='" . $field['value'] . "' ";
                if (isset($field['onclick']))
                    $element .= " onclick=\"javascript:" . $field['onclick'] . "\"";
                $element .= " >";
            }

            if ($field['type'] == 'text') {
                if (isset($values[$field['dbfield']]))
                    $element = stripslashes($values[$field['dbfield']]);
            }

            if ($field['type'] == 'tree_select') {
                $element = "<script>function ajax_" . $field['dbfield'] . "(pid){
 				new Ajax.Updater('c_" . $field['f_name'] . "','/ajax_server.php?type=" . $field['dbfield'] .
                    "&pid='+pid,{evalScripts:true});
 				}</script>";
                if (isset($values[$field['f_name']]))
                    $val = $values[$field['f_name']];
                else
                    $val = 0;
                $this->tr->dbTableData = $field['tree_name'];
                if (isset($values[$field['dbfield']])) {
                    $id = $values[$field['dbfield']];
                    $row = $this->tr->getRecord($id);
                    $pid = $row['pid'];
                    $l2s = $this->tr->get_select(array('pid' => $pid, 'fe_name' => $field['f_name'],
                        'id' => $id));
                } else {
                    $pid = 0;
                    $l2s = '&nbsp';
                }
                $element .= $this->tr->get_select(array('pid' => 0, 'id' => $pid,
                    'onchange_function' => 'ajax_' . $field['dbfield'], 'fe_name' => 'p' . $field['f_name']));
                $element .= '<div id="c_' . $field['f_name'] . '">' . $l2s . '</div>';
            }

            if ($field['type'] == 'callendar') {
                if (isset($values[$field['dbfield']])) {
                    if (strstr($values[$field['dbfield']], '-')) {
                        $v = split('-', $values[$field['dbfield']]);
                        $val = $v['2'] . '/' . $v['1'] . '/' . $v['0'];
                    } else
                        $val = $values[$field['dbfield']];
                } else
                    $val = '';
                $element = '<input type=edit value="' . $val . '" id="' . $field['f_name'] .
                    '" name="' . $field['f_name'] . '" ';
                if (isset($field['size']))
                    $element .= ' size="' . $field['size'] . '" ';
                if (isset($field['maxsize']))
                    $element .= ' maxsize="' . $field['size'] . '" ';
                $element .= ' ><a href="javascript:void(0)" onclick="return calGetDate(this, document.getElementById(\'' .
                    $field['f_name'] . '\'));"><img src="/js/callendar/prop.gif" width=16 height=16 border=0></a>';

            }

            if ($field['type'] == 'sql_checkbox') {
                //$element.='<select name="'.$field['f_name'].'">';
                $res2 = mysql_query($field['sql']);
                echo mysql_error();
                //print_r($values[$field['dbfield']]);
                while ($row2 = mysql_fetch_assoc($res2)) {
                    $element .= '<input type=checkbox name="' . $field['f_name'] . '[]" value="' . $row2['id'] .
                        '" ';
                    if (isset($values[$field['dbfield']])) {
                        if (in_array($row2['id'], $values[$field['dbfield']]))
                            $element .= 'checked';
                    }
                    $element .= ' >' . stripslashes($row2['name']) . '<br>';
                }
                //$element.='</select>';
                //if (isset($values[$field['dbfield']])) $element=$values[$field['dbfield']];
            }

            if ($field['type'] == 'sql_select') {
                $element .= '<select name="' . $field['f_name'] . '">';
                $res2 = mysql_query($field['sql']);
                if (!$res2)
                    echo $field['sql'] . " " . mysql_error();
                //print_r($values[$field['dbfield']]);
                $element .= '<option value="0">&nbsp;</option>' . "\n";
                while ($row2 = mysql_fetch_assoc($res2)) {
                    //$element.='<input type=checkbox name="'.$field['f_name'].'[]" value="'.$row2['id'].'" ';
                    $element .= '<option value="' . $row2['id'] . '" ';
                    //if (isset($values[$field['dbfield']])) { if (in_array($row2['id'],$values[$field['dbfield']])) $element.='selected'; }
                    if (isset($values[$field['dbfield']])) {
                        if ($values[$field['dbfield']] == $row2['id'])
                            $element .= 'selected';
                    }
                    $element .= ' >' . stripslashes($row2['name']) . '</option>';
                }
                $element .= '</select>';
                //if (isset($values[$field['dbfield']])) $element=$values[$field['dbfield']];
            }

            if ($field['type'] == 'fckeditor') {
                if (isset($values[$field['f_name']]))
                    $value = $values[$field['f_name']];
                else
                    $value = '';
                $oFCKeditor = new FCKeditor('FCKeditor');
                $oFCKeditor->BasePath = "/fckeditor/";
                $oFCKeditor->Config['SkinPath'] = '/fckeditor/editor/skins/silver/';
                $oFCKeditor->InstanceName = $field['f_name'];
                $oFCKeditor->Height = $field['height'];
                $oFCKeditor->Width = $field['width'];
                $oFCKeditor->Value = stripslashes($value);
                $oFCKeditor->ToolbarSet = 'MyToolbar';
                $element = $oFCKeditor->CreateHtml();
            }


            if ($field['type'] == 'image') {

                $element = '';
                $element .= '<table>';
                if (isset($values[$field['f_name']]))
                    $value = $values[$field['f_name']];
                else
                    $value = '';
                if ($value != '') {
                    $im = $this->img->get_image( /*$values[$field['f_name']]*/ $value);
                    $element .= '<tr><td><img src="' . $im['img_smallname'] . '"></td></tr>';
                    $element .= '<tr><td><input type="hidden" name="' . $field['f_name'] .
                        '" value="' . $im['img_id'] . '"></td></tr>';
                    //$element.='<tr><td><a href="'.$this->parent_url.'?field='.$field['f_name'].'&idel='.$im['img_id'].'&id='.$values['id'].'" >Удалить</a></td></tr>';
                    //'.$this->parent_url.'?field='.$field['f_name'].'&idel='.$im['img_id'].'
                    $element .= '<tr><td>удалить:<input type=checkbox name="Img[]" value="' . $field['f_name'] .
                        '-' . $im['img_id'] . '"></td></tr>';
                } else {
                    $element .= '<tr><td><input type=file name="' . $field['f_name'] .
                        '" size="40" style="width: 500px"></td></tr>';
                }
                $element .= '</table>';
                //$element='<input type=hidden name="delImgId" value="0">';
            }

            if ($field['type'] == 'checkbox') {
                $element = '';
                if (!isset($values[$field['f_name']]))
                    $values[$field['f_name']] = 0;
                if ($values[$field['f_name']] == 1)
                    $value = ' checked ';
                else
                    $value = '';
                $element .= '<input type=checkbox name="' . $field['f_name'] . '" value=1 ' . $value .
                    '>';
            }


            $name = "%" . $field['name'] . "%";
            $out = str_replace($name, $element, $out);
        }
        $out = str_replace('%PARENT_URL%', $this->parent_url, $out);
        $out = str_replace('%ERRORS%', $errors, $out);
        /*		if ($action=='add' || $action=='save') $out.="<input type=hidden name=a value='save'>";
        if ($action=='edit') {
        $out.="<input type=hidden name=a value='update'>";
        //$out.="<input type=hidden name=id value=".$values['id'].">";
        }*/
        echo $out;
    }


    function process_action($action, $pars)
    {
        $fields = $this->fields;
        global $HTTP_POST_FILES;
        if ($action == 'save') {
            $sql_start = " insert into " . $this->dbTableData . " (";
            $sql_field = '';
            $sql_value = '';
            while ($field = array_shift($fields)) {
                if (in_array($field['type'], $this->types)) {
                    if ($field['type'] == 'is_key') {
                        $sql_field .= $field['dbfield'];
                        $sql_ = "select IFNULL(max(" . $field['dbfield'] . "),0)+1 as id from " . $this->
                            dbTableData; //echo $sql_;
                        $res = $this->dbQuery($sql_);
                        $row = $this->dbFetchArray($res);
                        $sql_value .= $row['id'];
                    } else {
                        $sql_field .= "," . $field['dbfield'];
                    }

                    if ($field['type'] == 'edit' || $field['type'] == 'textarea' || $field['type'] ==
                        'fckeditor' )
                        $sql_value .= ",'" .addslashes($pars[$field['f_name']]). "'";
                    
					if ($field['type'] == 'checkbox')
                        $sql_value .= ",'" .((isset($pars[$field['f_name']]))?$pars[$field['f_name']]:0)."'";                        
                    
					if ($field['type'] == 'spr_select' || $field['type'] == 'tree_select' || $field['type'] ==
                        'sql_select')
                        $sql_value .= "," . $pars[$field['f_name']] . "";
                    if ($field['type'] == 'callendar') {
                        $date = split('/', $pars[$field['f_name']]);
                        $sql_value .= ",'" . $date[2] . "-" . $date[1] . "-" . $date[0] . "'";
                    }

                    if ($field['type'] == 'image') {
                    	//print_r($field);
                        $this->img->obj_id = 0;
                        $this->img->small_width = $field['resize'];
                        if (isset($field['resize2'])) {
                        	$this->img->middle_width = $field['resize2'];
                        	}
                        if (isset($HTTP_POST_FILES[$field['f_name']]['name'][0])) {
                            $v = $HTTP_POST_FILES[$field['f_name']];
                            $v['image_name'] = ' ';
                            $v['image_desc'] = ' ';

                            if (!$img_id = $this->img->upload_image($v)) {
                                echo " Загрузка изображения :" . $this->img->Error . "\n" . $this->img->Sql . "\n";
                            }
                            $sql_value .= ",'" . $img_id . "'";

                        } else {
                            $sql_value .= ",''";
                        }
                    }
                }
            }

            /*   				$sql_field.=",create_date";
            $sql_value.=",NOW()";

            if (isset($this->user_id)) {
            $sql_field.=",user_id";
            $sql_value.=",$this->user_id";
            }

            if (isset($this->tid)) {
            $sql_field.=",tid";
            $sql_value.=",$this->tid";
            }*/

            $sql = $sql_start . $sql_field . ") values " . $sql_value . ")";
            //echo $sql;
            return array('table' => $this->dbTableData, 'names' => $sql_field, 'values' => $sql_value);
        }

        if ($action == 'update') {
            $sql = ""; //"update $this->dbTableData set ";
            $delim = "";
            while ($field = array_shift($fields)) {
                if (in_array($field['type'], $this->types)) {
                    if (isset($field['is_key'])) {
                        $sql_end = " " . $field['dbfield'] . "=" . $pars[$field['f_name']];
                        $id = $pars[$field['f_name']];
                    } else {
                        if ($field['type'] == 'checkbox') {
                            if (!isset($pars[$field['f_name']]))
                                $pars[$field['f_name']] = 0;
                            // Если для чекбокса стоит атрибут is_unique, то перед обновлением обнуляются у всех записей данное поле
                            if ((isset($field['is_unique'])) && ($field['is_unique']==1)) {
                            	$keyid = $this->getIdFieldName();
                            	$this->dbQuery('update '.$this->dbTableData.' set '.$field['dbfield'].'=0');
                            }
                        }
                        if ($field['type'] == 'edit' || $field['type'] == 'textarea' || $field['type'] ==
                            'fckeditor')
                            $sql .= "$delim" . $field['dbfield'] . "='" . addslashes($pars[$field['f_name']]) .
                                "' ";
                        if ($field['type'] == 'spr_select' || $field['type'] == 'tree_select' || $field['type'] ==
                            'checkbox' || $field['type'] == 'sql_select')
                            $sql .= "$delim" . $field['dbfield'] . "=" . $pars[$field['f_name']] . " ";
                        if ($field['type'] == 'callendar') {
                            $date = split('/', $pars[$field['f_name']]);
                            $sql .= "$delim" . $field['dbfield'] . "='" . $date[2] . "-" . $date[1] . "-" .
                                $date[0] . "' ";
                        }

                        if ($field['type'] == 'image') {
                            $found = 0;
                            if (isset($pars['Img'])) { //print_r($pars['Img']);
                                foreach ($pars['Img'] as $im) {
                                    $img = split('-', $im); // print_r($img);
                                    if ($img[0] == $field['dbfield']) {
                                        if (!$this->img->delete_image($img[1])) {
                                            echo $this->img->getError();
                                        }
                                        $found = 1;
                                    }
                                }
                            }
                            //echo "++++$found+++";
                            if ($found == 1)
                                $sql .= "$delim" . $field['dbfield'] . "='' ";
                            else {
                                $this->img->obj_id = 0;
                                $this->img->small_width = $field['resize'];
                                if (isset($field['resize2'])) {
                        	        $this->img->middle_width = $field['resize2'];
                        			}
                                if (isset($HTTP_POST_FILES[$field['f_name']]['name'][0])) {
                                    //echo "+++++++++++++++";  print_r($HTTP_POST_FILES);
                                    foreach ($HTTP_POST_FILES as $v) {
                                        $v['image_name'] = ' ';
                                        $v['image_desc'] = ' ';
                                        if (!$img_id = $this->img->upload_image($v)) {
                                            echo " Загрузка изображения :" . $this->img->Error . "\n" . $this->img->Sql . "\n";
                                        }
                                        $sql .= "$delim" . $field['dbfield'] . "=" . $img_id . " ";
                                    }
                                } else {
                                    $sql .= "$delim" . $field['dbfield'] . "=" . $field['dbfield'] . " ";
                                }

                            }
                        } //if image

                        if ($delim == "")
                            $delim = ",";
                    }
                }
            }


            return array('table' => $this->dbTableData, 'names' => $sql, 'where' => $sql_end);
            $sql .= $sql_end;
            //echo $sql;

        }

        if ($action == 'delete') {
        $sqls = array();
            while ($field = array_shift($fields)) {
                if ($field['type'] == 'is_key') {
                    $sql = "select * from $this->dbTableData where " . $field['dbfield'] . "=" . $pars[$field['f_name']];
                    $res = mysql_query($sql);
                    $row = mysql_fetch_assoc($res);
                    $sql = "delete from $this->dbTableData where " . $field['dbfield'] . "=" . $pars[$field['f_name']];
                }

            }
            $fields = $this->fields;
            while ($field = array_shift($fields)) {
                if ($field['type'] == 'image') {
                    $this->img->delete_image($row[$field['dbfield']]);
                }
            }
			 		
		$res=$this->dbQuery($sql);
		//echo $sql.mysql_error();
		return $res;
        }


        /*		if ($action=='delete_image'){
        if (!$this->img->delete_image($pars['img_id'])) { return $this->img->getError(); }
        $sql = "delete from $this->dbTableData where ".$pars['field']."=".$pars['img_id'];
        return array('sql'=>$sql);


        }*/

        //echo "$sql";
        $res = $this->dbQuery($sql);
        if (!$res)
            return false;
        if ($action == 'save')
            return $this->InsertedId();
        return true;
    }

    function getNavigation($cat_id, $page, $sql_filter)
    {
        $items_on_page = 15;
        //if ($this->mode=='admin') $ind='sc_id'; else $ind='category';
        $sql = "select * from $this->dbTableData where  category_id=$cat_id $sql_filter";
        if ($this->mode == 'admin')
            $sql .= " ";
        else
            $sql .= " and status=1";
        //echo $sql;
        if (!$res = $this->dbQuery($sql))
            return false;
        $count = $this->dbNumRows($res);
        $out = " ";
        if ($count > 0) {
            $out = "<span class='nt'>Страницы: ";
            $pages = ceil($count / ($items_on_page));
            for ($i = 1; $i <= $pages; $i++) {
                if (($i / 30) == floor($i / 30))
                    $out .= "<br>";
                if ($page == $i)
                    $out .= "<a href='$this->parent_url?cat_id=$cat_id&page=$i' class=nt1><b>$i</b></a>&nbsp;";
                else
                    $out .= "<a href='$this->parent_url?cat_id=$cat_id&page=$i' class=nt1>$i</a>&nbsp;";
            }
            $out .= '</span>';
        } else
            $out = ' ';
        return $out;
    }

    function getRecord($ob_id)
    {
        $sql = "select * from $this->dbTableData where ob_id=$ob_id and (user_id=$this->user_id or '$this->mode'='admin')";
        $res = $this->dbQuery($sql);
        if (!$res)
            return false;
        $row = $this->dbFetchArray($res);
        return $row;
    }

    function publish($ob_id)
    {
        if ($this->mode == 'admin') {
            $sql = "update $this->dbTableData set status=1 where ob_id=$ob_id";
            $res = $this->dbQuery($sql);
            if (!$res)
                return false;
        }
        return true;
    }

}

?>