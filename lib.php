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
 * @author     Shen Zhang
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Shen Zhang, http://www.shenzhang.cn
 *
 */

defined('INTERNAL') || die();

require_once('group.php');
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

    public static function render_instance(BlockInstance $instance, $editing=false) {
		//random offset and limit amout set. This was copied from Mike Kelly's code
		$offset = 0;
		$limit = 2;
		
		//random group id set for this trial
		//$groupid = 3;
		
		//returns an array of all views for a group id. This uses the groupviews method that was copied acrossed
		$data = self::get_data($groupid);

		//the array that is returned contains a few other things but we just wanted to focus on sharedviews
		//the foreach below will loop through all views shared with the group
		foreach($data[sharedviews] as $aView){
			//the foreach below will loop through all the properties for a view (returned by get_data method) and assigns them to the required variables
			foreach($aView as $aViewProperty){
				//get the view
				$viewID = $aViewProperty[id]; //the page shared
				$fullurl = $aViewProperty[fullurl]; //full url of the page shared
				$viewTitle = $aViewProperty[displaytitle]; //view's title
				
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
		
		$items = array(
                'count' =>  $data[sharedviews]->count,
                'data'   => $contents,
                'offset' => $offset,
                'limit'  => $limit,
        );
		
		//calls Mike Kelly's method to build the objects to be displayed
		self::build_browse_list_html($items);
		
		
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
		
		//20140624 JW below code might be handy and thus not deleted yet
		/*
		$dwoo = smarty(
					array(
						'blocktype/groupviewsimage/js/jquery-ui/js/jquery-ui-1.8.19.custom.min.js',
						'blocktype/groupviewsimage/js/chosen.jquery.js',
						'blocktype/groupviewsimage/js/browse.js'
					),
					array(
						'<link href="' . get_config('wwwroot') . 'blocktype/groupviewsimage/js/jquery-ui/css/custom-theme/jquery-ui-1.8.20.custom.css" type="text/css" rel="stylesheet">',
						'<link href="' . get_config('wwwroot') . 'blocktype/groupviewsimage/theme/raw/static/style/chosen.css" type="text/css" rel="stylesheet">',
						'<link href="' . get_config('wwwroot') . 'blocktype/groupviewsimage/theme/raw/static/style/style.css" type="text/css" rel="stylesheet">'
					)
				);
		*/
		
		/*
		$smarty = smarty(array('artefact/browse/js/jquery-ui/js/jquery-ui-1.8.19.custom.min.js','artefact/browse/js/chosen.jquery.js','artefact/browse/js/browse.js'), array('<link href="' . get_config('wwwroot') . 'artefact/browse/js/jquery-ui/css/custom-theme/jquery-ui-1.8.20.custom.css" type="text/css" rel="stylesheet">','<link href="' . get_config('wwwroot') . 'artefact/browse/theme/raw/static/style/chosen.css" type="text/css" rel="stylesheet">'));
		$smarty->assign_by_ref('items', $items);
		$smarty->assign('PAGEHEADING', hsc(get_string("browse", "artefact.browse")));
		//$smarty->assign('colleges', $optionscolleges);
		$smarty->assign('INLINEJAVASCRIPT', $js);
		$smarty->display('artefact:browse:index.tpl');
		*/
		

    }

    public static function has_instance_config() {
        return true;
    }


    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
/*
            'showgroupviews' => array(
                'type' => 'radio',
                'description' => get_string('displaygroupviewsdesc', 'blocktype.groupviewsimage'),
                'title' => get_string('displaygroupviews', 'blocktype.groupviewsimage'),
                'options' => array(
                    1 => get_string('yes'),
                    0 => get_string('no'),
                ),
                'separator' => '<br>',
                'defaultvalue' => isset($configdata['showgroupviews']) ? $configdata['showgroupviews'] : 1,
            ),
*/
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

	/** Code copied from blocktype::groupviews **/
	protected static function get_data($groupid) {
        global $USER;

        if(!defined('GROUP')) {
            define('GROUP', $groupid);
        }
        // get the currently requested group
        $group = group_current_group();
        $role = group_user_access($group->id);
        if ($role) {
            // For group members, display a list of views that others have
            // shared to the group
            $data['sharedviews'] = View::get_sharedviews_data(null, 0, $group->id);
            foreach ($data['sharedviews']->data as &$view) {
                if (isset($view['template']) && $view['template']) {
                    $view['form'] = pieform(create_view_form($group, null, $view->id));
                }
            }
			
			//20140624 JW the below code is commented out as all we care about is the views shared to the group
			// Get all views created in the group
			/*
            $sort = array(array('column' => 'type=\'grouphomepage\'', 'desc' => true));
            $data['groupviews'] = View::view_search(null, null, (object) array('group' => $group->id), null, null, 0, true, $sort);
            foreach ($data['groupviews']->data as &$view) {
                if (isset($view['template']) && $view['template']) {
                    $view['form'] = pieform(create_view_form(null, null, $view['id']));
                }
            }
			*/
            
			/*
            if (group_user_can_assess_submitted_views($group->id, $USER->get('id'))) {
                // Display a list of views submitted to the group
                list($collections, $views) = View::get_views_and_collections(null, null, null, null, false, $group->id);
                $data['allsubmitted'] = array_merge(array_values($collections), array_values($views));
            }
			*/
        }
		/*
        if ($group->submittableto) {
            require_once('pieforms/pieform.php');
            // A user can submit more than one view to the same group, but no view can be
            // submitted to more than one group.

            // Display a list of views this user has submitted to this group, and a submission
            // form containing drop-down of their unsubmitted views.

            list($collections, $views) = View::get_views_and_collections($USER->get('id'), null, null, null, false, $group->id);
            $data['mysubmitted'] = array_merge(array_values($collections), array_values($views));

            $data['group_view_submission_form'] = group_view_submission_form($group->id);
        }
		*/
        $data['group'] = $group;
        return $data;
    }
	/** End of code copied from blocktype::groupviews **/    
    
    
/*
    protected static function get_data($groupid) {
        global $USER;

        if(!defined('GROUP')) {
            define('GROUP', $groupid);
        }
        // get the currently requested group
        $group = group_current_group();
        $role = group_user_access($group->id);
        if ($role) {
            // Get all views created in the group
            $sort = array(array('column' => 'type=\'grouphomepage\'', 'desc' => true));
            $data['groupviews'] = View::view_search(null, null, (object) array('group' => $group->id), null, null, 0, true, $sort);
            foreach ($data['groupviews']->data as &$view) {
                if (isset($view['template']) && $view['template']) {
                    $view['form'] = pieform(create_view_form(null, null, $view['id']));
                }
            }

            // For group members, display a list of views that others have
            // shared to the group
            $data['sharedviews'] = View::get_sharedviews_data(null, 0, $group->id);
            foreach ($data['sharedviews']->data as &$view) {
                if (isset($view['template']) && $view['template']) {
                    $view['form'] = pieform(create_view_form($group, null, $view->id));
                }
            }

            if (group_user_can_assess_submitted_views($group->id, $USER->get('id'))) {
                // Display a list of views submitted to the group
                list($collections, $views) = View::get_views_and_collections(null, null, null, null, false, $group->id);
                $data['allsubmitted'] = array_merge(array_values($collections), array_values($views));
            }
        }

        if ($group->submittableto) {
            require_once('pieforms/pieform.php');
            // A user can submit more than one view to the same group, but no view can be
            // submitted to more than one group.

            // Display a list of views this user has submitted to this group, and a submission
            // form containing drop-down of their unsubmitted views.

            list($collections, $views) = View::get_views_and_collections($USER->get('id'), null, null, null, false, $group->id);
            $data['mysubmitted'] = array_merge(array_values($collections), array_values($views));

            $data['group_view_submission_form'] = group_view_submission_form($group->id);
        }
        $data['group'] = $group;
        return $data;
    }

*/
    public static function get_instance_title() {
        return get_string('title', 'blocktype.groupviewsimage');
    }



	/**
	* Start of Mike Kelly's code
	*/
	public static function build_browse_list_html(&$items) {
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
	
	

	//echo $items['pagination'];
	//echo $items['pagination_js'];
    }
	
	/**
	* Builds pagination links for HTML display.
	*
	* @param array $params Options for the pagination
	*/
	function build_browse_pagination($params) {


	
	    echo "<pre>".print_r($params,true)."</pre>";
	    
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

		
		if ($params['limit'] <= $params['count']) {
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
			$output .= self::build_browse_pagination_pagelink('first', $params['url'], $setlimit, $params['limit'], 0, '&laquo; ' . $params['firsttext'], get_string('firstpage'), $isfirst, $params['offsetname']);
			$output .= self::build_browse_pagination_pagelink('prev', $params['url'], $setlimit, $params['limit'], $params['limit'] * $prev, $params['offset'], '&larr; ' . $params['previoustext'], get_string('prevpage'), $isfirst, $params['offsetname']);

			// Build the pagenumbers in the middle
			foreach ($pagenumbers as $k => $i) {
				if ($k != 0 && $prevpagenum < $i - 1) {
					$output .= 'É';
				}
				if ($i == $page) {
					$output .= '<span class="selected">' . ($i + 1) . '</span>';
				}
				else {
					$output .= self::build_browse_pagination_pagelink('', $params['url'], $setlimit, $params['limit'],
						$params['limit'] * $i, $i + 1, '', false, $params['offsetname']);
				}
				$prevpagenum = $i;
			}

			// Build the next/last links
			$islast = $page == $last;
			$output .= self::build_browse_pagination_pagelink('next', $params['url'], $setlimit, $params['limit'], $params['limit'] * $next,
				$params['nexttext'] . ' &rarr;', get_string('nextpage'), $islast, $params['offsetname']);

		}

		$js = '';
		// Close the container div
		$output .= '</div>';

		return array('html' => $output, 'javascript' => $js);

	}

	function build_browse_pagination_pagelink($class, $url, $setlimit, $limit, $offset, $text, $title, $disabled=false, $offsetname='offset') {
		$return = '<span class="pagination';
		$return .= ($class) ? " $class" : '';
		$url = "javascript:groupviewsimage.filtercontent('recentwork'," . $limit . "," . $offset . ");";

		if ($disabled) {
			$return .= ' disabled">' . $text . '</span>';
		}
		else {
			$return .= '">'
			. '<a href="' . $url . '" title="' . $title
			. '">' . $text . '</a></span>';
		}

		return $return;
	}
	/** End of Mike Kelly's code **/
	
	
}	