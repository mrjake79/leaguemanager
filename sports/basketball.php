<?php
/**
 * Basketball Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerBasketball extends LeagueManager
{

	/**
	 * sports keys
	 *
	 * @var array
	 */
	var $key = 'basketball';

	/**
	 * load specifif settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'rank_teams_'.$this->key, array(&$this, 'rankTeams') );
		add_filter( 'team_points2_'.$this->key, array(&$this, 'calculateBasketStatistics') );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0 );
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'leaguemanager_standings_header_admin_'.$this->key, array(&$this, 'displayStandingsAdminHeader') );
		add_action( 'leaguemanager_standings_columns_admin_'.$this->key, array(&$this, 'displayStandingsAdminColumns'), 10, 2 );
	}
	function LeagueManagerHockey()
	{
		$this->__construct();
	}


	/**
	 * add sports to list
	 *
	 * @param array $sports
	 * @return array
	 */
	function sports( $sports )
	{
		$sports[$this->key] = __( 'Basketball', 'leaguemanager' );

		return $sports;
	}


	/**
	 * rank Teams
	 *
	 * @param array $teams
	 * @return array of teams
	 */
	function rankTeams( $teams )
	{
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row->points['plus'];
			$done[$key] = $row->done_matches;
			$diff[$key] = $row->diff;
		}

		array_multisort( $points, SORT_DESC, $diff, SORT_DESC, $done, SORT_ASC, $teams );
		return $teams;
	}


	/**
	 * calculate goals. Penalty is not counted in statistics
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateBasketStatistics( $team_id )
	{
		global $wpdb;
		
		$goals = array( 'plus' => 0, 'minus' => 0 );
				
		$matches = $wpdb->get_results( "SELECT `home_points`, `away_points`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."'" );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				$custom = maybe_unserialize($match->custom);
				if ( !empty($custom['overtime']['home']) && !empty($custom['overtime']['away']) ) {
					$home_goals = $custom['overtime']['home'];
					$away_goals = $custom['overtime']['away'];
				} else {
					$home_goals = $match->home_points;
					$away_goals = $match->away_points;
				}
				
				$goals['plus'] += $home_goals;
				$goals['minus'] += $away_goals;
			}
		}
		
		$matches = $wpdb->get_results( "SELECT `home_points`, `away_points`, `overtime` FROM {$wpdb->leaguemanager_matches} WHERE `away_team` = '".$team_id."'" );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				$custom = maybe_unserialize($match->custom);
				if ( !empty($custom['overtime']['home']) && !empty($custom['overtime']['minus']) ) {
					$home_goals = $custom['overtime']['home'];
					$away_goals = $custom['overtime']['away'];
				} else {
					$home_goals = $match->home_points;
					$away_goals = $match->away_points;
				}
				
				$goals['plus'] += $away_goals;
				$goals['minus'] += $home_goals;
			}
		}
		
		return $goals;
	}


	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsAdminHeader()
	{
		echo '<th class="num">'._c( 'Baskets', 'leaguemanager' ).'</th><th>'.__( 'Diff', 'leaguemanager').'</th>';
	}


	/**
	 * extend columns for Standings Table in Backend
	 *
	 * @param object $team
	 * @param string $rule
	 * @return void
	 */
	function displayStandingsAdminColumns( $team, $rule )
	{
		echo '<td class="num">';
		if ( $rule != 'manual' ) {
			printf('%d:%d', $team->points2['plus'], $team->points2['minus']);
		} else {
			echo '<input type="text" size="2" name="custom['.$team->id.'][points2][plus]" value="'.$team->points2['plus'].'" /> : <input type="text" size="2" name="custom['.$team->id.'][points2][minus]" value="'.$team->points2['minus'].'" />';
		}
		echo '</td>';
		echo '<td class="num">'.$team->diff.'</td>';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__( 'Quarters', 'leaguemanager' ).'</th><th>'.__( 'Overtime', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		echo '<td>';
		for ( $i = 1; $i <= 4; $i++ )
			echo '<input class="points" type="text" size="2" id="quarters_plus_'.$i.'_'.$match->id.'" name="custom['.$match->id.'][quarters]['.$i.'][plus]" value="'.$match->quarters[$i]['plus'].'" /> : <input clas="points" type="text" size="2" id="quarters_minus_'.$i.'_'.$match->id.'" name="custom['.$match->id.'][quarters]['.$i.'][minus]" value="'.$match->quarters[$i]['minus'].'" /><br />';
		echo '</td>';

		echo '<td><input class="points" type="text" size="2" id="overtime_home_'.$match->id.'" name="custom['.$match->id.'][overtime][home]" value="'.$match->overtime['home'].'" /> : <input class="points" type="text" size="2" id="overtime_away_'.$match->id.'" name="custom['.$match->id.'][overtime][away]" value="'.$match->overtime['away'].'" /></td>';
	}
}

$basketball = new LeagueManagerBasketball();
?>
