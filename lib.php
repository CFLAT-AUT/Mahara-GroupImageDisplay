<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-groupviewsimage
 * @author     Shen Zhang / Jawyei Wong, AUT University, Code adapted from artefact-browse plugin by Mike Kelly UAL m.f.kelly@arts.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL

 *
 */

defined('INTERNAL') || die();

require_once('group.php');
require_once('view.php'); //20140626 JW might be needed for pagniation
class PluginBlocktypeGroupViewsImage extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.groupviewsimage');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.groupviewsimage');
    }

    public static function single_only() {
        return false;
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('grouphomepage');
    }

    public static function hide_title_on_empty_content() {
        return true;
    }
	
	public static function has_instance_config() {
        return true;
    }
	
	public static function get_instance_title() {
        return get_string('title', 'blocktype.groupviewsimage');
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
            'showsharedviews' => array(
                'type' => 'radio',
                'title' => get_string('displaysharedviews', 'blocktype.groupviewsimage'),
                'description' => get_string('displaysharedviewsdesc', 'blocktype.groupviewsimage'),
                'options' => array(
                    1 => get_string('yes'),
                    0 => get_string('no'),
                ),
                'separator' => '<br>',
                'defaultvalue' => isset($configdata['showsharedviews']) ? $configdata['showsharedviews'] : 1,
            ),
        );
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
		//Default limit and offset. This was copied from Mike Kelly's code
		$offset = 0;
		$limit = 10;
		
		 $groupid = $instance->get_view()->get('group');
		 if (!$groupid) {
            return '';
        }
		
		//returns an array of all views for a group id. This uses the groupviews method that was copied acrossed
		$items = self::get_data($groupid, $offset, $limit);
		
		//calls Mike Kelly's method to build the objects to be displayed
		self::build_browse_list_html($items);
		
		if(empty($items) || !isset($items)){
			return "You are not a member of this group";
		}
		
		//Not sure what this does
		$js = <<< EOF
addLoadEvent(function () {
    {$items['pagination_js']}
});
EOF;
		
		//neededJS and neededCSS are files required to render the page properly. They are done this way so it is easier to read
		$neededJS = array(
			get_config('wwwroot').'blocktype/groupviewsimage/js/jquery-ui/js/jquery-ui-1.8.19.custom.min.js',
			get_config('wwwroot').'blocktype/groupviewsimage/js/chosen.jquery.js',
			get_config('wwwroot').'blocktype/groupviewsimage/js/browse.js'
		);
		$neededCSS = array(
			get_config('wwwroot')."blocktype/groupviewsimage/js/jquery-ui/css/custom-theme/jquery-ui-1.8.20.custom.css",
			get_config('wwwroot')."blocktype/groupviewsimage/theme/raw/static/style/chosen.css",
			get_config('wwwroot')."blocktype/groupviewsimage/theme/raw/static/style/style.css"
		);
		
		//calls smarty and feeds the data to be render via the tpl file
		$dwoo = smarty_core();
		
		//adds the javascript and css files into the template to be rendered
		$dwoo->assign('JAVASCRIPT', $neededJS);
		$dwoo->assign('STYLESHEETLIST', $neededCSS);
		$dwoo->assign('INLINEJAVASCRIPT', $js); //????
		
		//adds the items into the templated to be rendered
		$dwoo->assign_by_ref('items', $items);

		//returns the template as text so mahara can render the block
		return $dwoo->fetch('blocktype:groupviewsimage:index.tpl');
    }

	/** Code copied from blocktype::groupviews **/
	public static function get_data($groupid, $offset=0, $limit=20) {
		global $USER;
		$texttitletrim = 20;
		
        if(!defined('GROUP')) {
          define('GROUP', $groupid);
        }
        // get the currently requested group
        $group = group_current_group();
        $role = group_user_access($group->id);
		
        if ($role) {		
			// For group members, display a list of views that others have shared to the group
			// Params for get_sharedviews_data is $limit=0,$offset=0, $groupid
       
			$data['sharedviews'] = View::get_sharedviews_data($limit, $offset, $group->id);
            
			/*
			foreach ($data['sharedviews']->data as &$view) {
				if (isset($view['template']) && $view['template']) {
					$view['form'] = pieform(create_view_form($group, null, $view->id));
				}				
            }
			*/
			
			//the array that is returned from View::get_sharedviews_data($limit, $offset, $group->id)
			//contains a few other things but we just wanted to focus on sharedviews
			//the foreach below will loop through all views shared with the group
			foreach($data[sharedviews] as $aView){

				//20140909 JW sort the array returned by View::get_sharedviews_data($limit, $offset, $group->id)
				//by ctime, this requires the query within get_sharedviews_data to have ctime in its select string
				$aView = self::array_msort($aView, array('ctime'=>SORT_DESC));
			
				//the foreach below will loop through all the properties for a view (returned by get_data method) and assigns them to the required variables
				foreach($aView as $aViewProperty){
					//get the view
					$viewID = $aViewProperty[id]; //the page shared
					$fullurl = $aViewProperty[fullurl]; //full url of the page shared
					$viewTitle = str_shorten_text($aViewProperty[displaytitle], $texttitletrim, true); //view's title
					
					//get the owner of the view
					$viewOwnerName = $aViewProperty[user]->firstname." ".$aViewProperty[user]->lastname; //owner of the view's name
					$userobj = new User();
					$userobj->find_by_id($aViewProperty[user]->id);
					$profileurl = profile_url($userobj); //owner of the view's proflie page
					$avatarurl = profile_icon_url($aViewProperty[user]->id,50,50); //owner of the view's profile picture
					
					
					//get the artefact id of an image in the view
					$theView = new View($aViewProperty[id]); //create the view
					$artefactInView = $theView->get_artefact_metadata(); //get all artefacts in the view
					foreach($artefactInView as $anArtefact){ //for each artefact
						if($anArtefact->artefacttype == 'image'){
							$artefactID = $anArtefact->id; //if it is an image artefact assign the id and break the loop
							break;
						}
						
						//20150331 JW added that if page contains a folder with images (galleries count as folders)
						//it will pull an image from that folder and use it as the cover
						if($anArtefact->artefacttype == 'folder'){
							$query = "SELECT id FROM {artefact} where parent = ? AND artefacttype = 'image'";
							$imagesInAFolder = get_records_sql_array($query,array($anArtefact->id));
							
							//only assign the id of an image if the folder contains at least 1 image
							if(!empty($imagesInAFolder)){
								$artefactID = $imagesInAFolder[0]->id;
								break;
							}
						}
						
						//20140903 JW if there are no images on the page then set to artefactID to 0
						//this way, when display each page, instead of a blank box it will show a place holder image
						$artefactID = 0;
					}
					
					//the items variable below requires the contents array to be in this format
					$contents['photos'][] = array(
						"image" => array (
								"id" => $artefactID,
								"view" => $viewID
								),
						"type" => "photo",
						"page" => array(
									"url" => $fullurl,
									"title" => $viewTitle
						),
						"owner" => array(
									"name" => $viewOwnerName,
									"profileurl" => $profileurl,
									"avatarurl" => $avatarurl
						)
					);
				}
			}
			
			$items2 = array(
					'count'	 => $data[sharedviews]->count,
					'data'   => $contents,
					'offset' => $offset,
					'limit'  => $limit,
			);
        }
		
        //$data['group'] = $group;
		
        return $items2;
    }
	/** End of code copied from blocktype::groupviews **/    
    
	/**
	* 20140909 JW
	* array_msort copied from http://php.net/manual/en/function.array-multisort.php
	* This is used to sort array returned by View::get_sharedviews_data($limit, $offset, $group->id)
	*/
	private static function array_msort($array, $cols){
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$eval = 'array_multisort(';
		foreach ($cols as $col => $order) {
			$eval .= '$colarr[\''.$col.'\'],'.$order.',';
		}
		$eval = substr($eval,0,-1).');';
		eval($eval);
		$ret = array();
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				$k = substr($k,1);
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
		}
		return $ret;
	}
	
    /**
	* Start of Mike Kelly's code
	*/
	public static function build_browse_list_html(&$items) {
        //20140627 JW added if set so it does not display anything if the user is not a group member
		if(isset($items)){
			$smarty = smarty_core();
			$smarty->assign_by_ref('items', $items);
			$smarty->assign('wwwroot', get_config('wwwroot'));
			$items['tablerows'] = $smarty->fetch('blocktype:groupviewsimage:browselist.tpl'); // the 'tablerows' naming is required for pagination script
			$pagination = self::build_browse_pagination(array(
				'id' => 'browselist_pagination',
				'url' => get_config('wwwroot') . 'blocktype/groupviewsimage/lib.php',
				'jsonscript' => 'blocktype/groupviewsimage/browse.json.php',
				'datatable' => 'browselist', // the pagination script expects a table with this id
				'count' => $items['count'],
				'limit' => $items['limit'],
				'offset' => $items['offset'],
				'firsttext' => '',
				'previoustext' => '',
				'nexttext' => '',
				'lasttext' => '',
				'numbersincludefirstlast' => false,
				'resultcounttextsingular' => 'Item', //get_string('plan', 'artefact.plans'),
				'resultcounttextplural' => 'Items', //get_string('plans', 'artefact.plans'),
			));
			$items['pagination'] = $pagination['html'];
			$items['pagination_js'] = $pagination['javascript'];
		}
    }
	
	/**
	* Builds pagination links for HTML display.
	*
	* @param array $params Options for the pagination
	*/
	function build_browse_pagination($params) {
		
		// Bail if the required attributes are not present
		$required = array('url', 'count', 'limit', 'offset');
		foreach ($required as $option) {
			if (!isset($params[$option])) {
				throw new ParameterException('You must supply option "' . $option . '" to build_pagination');
			}
		}

		// Work out default values for parameters
		if (!isset($params['id'])) {
			$params['id'] = substr(md5(microtime()), 0, 4);
		}

		$params['offsetname'] = (isset($params['offsetname'])) ? $params['offsetname'] : 'offset';
		if (isset($params['forceoffset']) && !is_null($params['forceoffset'])) {
			$params['offset'] = (int) $params['forceoffset'];
		}
		else if (!isset($params['offset'])) {
			$params['offset'] = param_integer($params['offsetname'], 0);
		}

		// Correct for odd offsets
		$params['offset'] -= $params['offset'] % $params['limit'];

		$params['firsttext'] = (isset($params['firsttext'])) ? $params['firsttext'] : get_string('first');
		$params['previoustext'] = (isset($params['previoustext'])) ? $params['previoustext'] : get_string('previous');
		$params['nexttext']  = (isset($params['nexttext']))  ? $params['nexttext'] : get_string('next');
		$params['resultcounttextsingular'] = (isset($params['resultcounttextsingular'])) ? $params['resultcounttextsingular'] : get_string('result');
		$params['resultcounttextplural'] = (isset($params['resultcounttextplural'])) ? $params['resultcounttextplural'] : get_string('results');

		if (!isset($params['numbersincludefirstlast'])) {
			$params['numbersincludefirstlast'] = true;
		}
		if (!isset($params['numbersincludeprevnext'])) {
			$params['numbersincludeprevnext'] = true;
		}

		if (!isset($params['extradata'])) {
			$params['extradata'] = null;
		}

		// Begin building the output
		$output = '<div id="' . $params['id'] . '" class="pagination';
		if (isset($params['class'])) {
			$output .= ' ' . hsc($params['class']);
		}
		$output .= '">';

		//20140630 JW removed <= and added <
		if ($params['limit'] < $params['count']) {
			$pages = ceil($params['count'] / $params['limit']);
			$page = $params['offset'] / $params['limit'];

			$last = $pages - 1;
			if (!empty($params['lastpage'])) {
				$page = $last;
			}
			$next = min($last, $page + 1);
			$prev = max(0, $page - 1);

			// Build a list of what pagenumbers will be put between the previous/next links
			$pagenumbers = array();
			if ($params['numbersincludefirstlast']) {
				$pagenumbers[] = 0;
			}
			if ($params['numbersincludeprevnext']) {
				$pagenumbers[] = $prev;
			}
			$pagenumbers[] = $page;
			if ($params['numbersincludeprevnext']) {
				$pagenumbers[] = $next;
			}
			if ($params['numbersincludefirstlast']) {
				$pagenumbers[] = $last;
			}
			$pagenumbers = array_unique($pagenumbers);

			// Build the first/previous links
			$isfirst = $page == 0;
			$setlimit = true;
			$output .= self::build_browse_pagination_pagelink('first', 
																$params['url'], 
																$setlimit, 
																$params['limit'], 
																0, 
																'&laquo; First ' . $params['firsttext'], 
																get_string('firstpage'), 
																$isfirst, 
																$params['offsetname']
															);
			
			$output .= self::build_browse_pagination_pagelink('prev', 
																$params['url'], 
																$setlimit, 
																$params['limit'], 
																$params['limit'] * $prev, 
																'&larr; Previous ' . $params['previoustext'], 
																get_string('prevpage'), 
																$isfirst, 
																$params['offsetname']
															);
															
			// Build the pagenumbers in the middle
			foreach ($pagenumbers as $k => $i) {
				if ($k != 0 && $prevpagenum < $i - 1) {
					$output .= 'É';
				}
				if ($i == $page) {
					$output .= '<span class="selected">' . ($i + 1) . '</span>';
				}
				else {
					$output .= self::build_browse_pagination_pagelink('', 
																		$params['url'], 
																		$setlimit, 
																		$params['limit'],
																		$params['limit'] * $i, 
																		$i + 1, 
																		'', 
																		false, 
																		$params['offsetname']
																	);
				}
				$prevpagenum = $i;
			}

			// Build the next/last links
			$islast = $page == $last;
			$output .= self::build_browse_pagination_pagelink('next', 
																$params['url'], 
																$setlimit, 
																$params['limit'], 
																$params['limit'] * $next,
																$params['nexttext'] . ' Next &rarr;', 
																get_string('nextpage'), 
																$islast, $params['offsetname']
															);

		}

		$js = '';
		// Close the container div
		$output .= '</div>';

		return array('html' => $output, 'javascript' => $js);

	}

	function build_browse_pagination_pagelink($class, $url, $setlimit, $limit, $offset, $text, $title, $disabled=false, $offsetname='offset') {
		$url = "javascript:Browse.filtercontent('recentwork'," . $limit . "," . $offset . ");";
		//$url = "javascript:groupviewsimage.filtercontent('recentwork'," . $limit . "," . $offset . ");";

		if ($disabled) {
			//20140627 JW display nothing if the button is disabled
			//$return = '<span class="pagination';
			//$return .= ($class) ? " $class" : '';
			//$return .= ' disabled">' . $text . '</span>';
		}
		else {
			$return = '<span class="pagination';
			$return .= ($class) ? " $class" : '';
			$return .= '">'
			. '<a href="' . $url . '" title="' . $title
			. '">' . $text . '</a></span>';
		}

		return $return;
	}
	/** End of Mike Kelly's code **/
	
	
}	