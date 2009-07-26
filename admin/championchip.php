<?php
global $championchip;

$finalkey = isset($_GET['final']) ? $_GET['final'] : $championchip->getFinalKeys(1);

$league = $championchip->getLeague();
$season = $leaguemanager->getSeason( $league );
$num_first_round = $championchip->getNumTeamsFirstRound();

if ( isset($_POST['updateResults']) ) {
	if ( is_string(end($_POST['home_team'])) ) {
		$leaguemanager->setMessage(__( "It seems the previous round is not over yet.", 'leaguemanager'), true);
		$leaguemanager->printMessage();
	} else {
		$custom = isset($_POST['custom']) ? $_POST['custom'] : false;
		$championchip->updateResults($_POST['league_id'], $_POST['matches'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'], $custom, $_POST['round']);

	}
}
?>

<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Championchip Finals', 'leaguemanager') ?></p>

	<h2><?php _e( 'Championchip Results', 'leaguemanager' ) ?></h2>
	
	<form method="post" action="">
	<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
	
	<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><?php _e( 'Round', 'leaguemanger' ) ?></th>
		<th scope="col" colspan="<?php echo ($num_first_round > 4) ? 4 : $num_first_round; ?>" style="text-align: center;"><?php _e( 'Matches', 'leaguemanager' ) ?></td>
	</tr>
	<tbody id="the-list-finals" class="form-table">
	<?php foreach ( $championchip->getFinals() AS $i => $final ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
	<?php
		if ( $matches = $leaguemanager->getMatches("`league_id` = '".$league->id."' AND `final` = '".$final['key']."'", false, "`id` ASC") ) {
			$teams = $leaguemanager->getTeams( "league_id = '".$league->id."' AND `season` = '".$season['name']."'", 'ARRAY' );
			$teams2 = $championchip->getFinalTeams( $final, 'ARRAY' );
		}
	?>
		<tr class="<?php echo $class ?>">
			<th scope="row"><strong><?php echo $final['name'] ?></strong></th>
			<?php for ( $i = 1; $i <= $final['num_matches']; $i++ ) : $match = $matches[$i-1]; ?>
			<?php $colspan = ( $num_first_round > 4 && $finalkey == 'final') ? 4 : ($num_first_round/4)/$final['num_matches']; ?>

			<td colspan="<?php echo $colspan ?>" style="text-align: center;">
				<?php if ( $match ) : ?>

				<?php if ( isset($teams[$match->home_team]) && isset($teams[$match->away_team]) ) : ?>
					<p><?php printf('%s &#8211; %s', $teams[$match->home_team]['title'], $teams[$match->away_team]['title']) ?></p>
					<?php if ( $match->home_points != NULL && $match->away_points != NULL ) : ?>
						<p><strong><?php printf($league->point_format, $match->home_points, $match->away_points) ?></strong></p>
					<?php else : ?>
						<p>-:-</p>
					<?php endif; ?>
				<?php else : ?>
					&#8211;
				<?php endif; ?>

				<?php endif; ?>
			</td>
			<?php if ( $i%4 == 0 && $i < $final['num_matches'] ) : ?>
			</tr><tr class="<?php echo $class ?>"><th>&#160;</th>
			<?php endif; ?>

			<?php endfor; ?>
		</tr>
	<?php endforeach ?>
	</tbody>
	</table>
	</form>
	
	
	<h2><?php printf(__( 'Championchip Finals &#8211; %s', 'leaguemanager' ), $championchip->getFinalName($finalkey)) ?></h2>

	<div class="tablenav">
	<form action="admin.php" method="get" style="display: inline;">
		<input type="hidden" name="page" value="leaguemanager" />
		<input type="hidden" name="subpage" value="<?php echo $championchip->getPageKey() ?>" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

		<select size="1" name="final" id="final">
			<?php foreach ( $championchip->getFinals() AS $final ) : ?>
			<option value="<?php echo $final['key'] ?>"<?php selected($finalkey, $final['key']) ?>><?php echo $final['name'] ?></option>	
			<?php endforeach; ?>
		</select>
		<input type="submit" class="button-secondary" value="<?php _e( 'Show', 'leaguemanager' ) ?>" />
	</form>
	<form action="admin.php" method="get" style="display: inline;">
		<input type="hidden" name="page" value="leaguemanager" />
		<input type="hidden" name="subpage" value="match" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

		<!-- Bulk Actions -->
		<select name="mode" size="1">
			<option value="-1" selected="selected"><?php _e('Actions', 'leaguemanager') ?></option>
			<option value="add"><?php _e('Add Matches', 'leaguemanager')?></option>
			<option value="edit"><?php _e( 'Edit Matches', 'leaguemanager' ) ?></option>
		</select>

		<select size="1" name="final" id="final1">
		<?php foreach ( $championchip->getFinals() AS $final ) : ?>
			<option value="<?php echo $final['key'] ?>"><?php echo $final['name'] ?></option>
		<?php endforeach; ?>
		</select>
		<input type="submit" class="button-secondary" value="<?php _e( 'Go', 'leaguemanager' ) ?>" />
	</form>
	</div>

	<?php $final = $championchip->getFinals($finalkey); ?>
	<!--<h3><?php echo $final['name'] ?></h3>-->
	<?php $teams = $leaguemanager->getTeams( "league_id = '".$league->id."' AND `season` = '".$season['name']."'", 'ARRAY' ); ?>
	<?php $teams2 = $championchip->getFinalTeams( $final, 'ARRAY' ); ?>
	<?php $matches = $leaguemanager->getMatches("`league_id` = '".$league->id."' AND `final` = '".$final['key']."'", false, "`id` ASC"); ?>

	<form method="post" action="">
	<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
	<input type="hidden" name="round" value="<?php echo $final['round'] ?>" />
	<table class="widefat">
	<thead>
	<tr>
		<th><?php _e( '#', 'leaguemanager' ) ?></th>
		<th><?php _e( 'Date','leaguemanager' ) ?></th>
		<th><?php _e( 'Match','leaguemanager' ) ?></th>
		<th><?php _e( 'Location','leaguemanager' ) ?></th>
		<th><?php _e( 'Begin','leaguemanager' ) ?></th>
		<th><?php _e( 'Score', 'leaguemanager' ) ?></th>
		<?php do_action( 'matchtable_header_'.$league->sport ); ?>
	</tr>
	</thead>
	<tbody id="the-list-<?php echo $final['key'] ?>" class="form-table">
	<?php for ( $i = 1; $i <= $final['num_matches']; $i++ ) : $match = $matches[$i-1]; ?>
		<?php if ( is_string($match->home_team) && is_string($match->away_team) )
			$title = $teams2[$match->home_team] . " &#8211; " . $teams2[$match->away_team];
		      else
			$title = $teams[$match->home_team]['title'] . " &#8211; " . $teams[$match->away_team]['title'];
		?>
		<tr class="<?php echo $class ?>">
			<td><?php echo $i ?><input type="hidden" name="matches[<?php echo $match->id ?>]" value="<?php echo $match->id ?>" /><input type="hidden" name="home_team[<?php echo $match->id ?>]" value="<?php echo $match->home_team ?>" /><input type="hidden" name="away_team[<?php echo $match->id ?>]" value="<?php echo $match->away_team ?>" /></td>
			<td><?php echo ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date(get_option('date_format'), $match->date) ?></td>
			<td><?php echo $title ?></td>
			<td><?php echo ( '' == $match->location ) ? 'N/A' : $match->location ?></td>
			<td><?php echo ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date) ?></td>
			<td>
				<input class="points" type="text" size="2" id="home_points_<?php echo $match->id ?>_regular" name="home_points[<?php echo $match->id ?>]" value="<?php echo $match->home_points ?>" /> : <input class="points" type="text" size="2" id="away_points[<?php echo $match->id ?>]" name="away_points[<?php echo $match->id ?>]" value="<?php echo $match->away_points ?>" />
			</td>
			<?php do_action( 'matchtable_columns_'.$league->sport, $match ) ?>
		</tr>
	<?php endfor; ?>
	</tbody>
	</table>

	<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Save Results','leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>


	<h2><?php _e( 'Preliminary Rounds Standings', 'leaguemanager' ) ?></h2>
	<?php foreach ( $championchip->getGroups() AS $key => $group ) : ?>
	<?php $teams = $leaguemanager->getTeams( "`league_id` = '".$league->id."' AND `season` = '".$season['name']."' AND `group` = '".$group."'" ); ?>
	<div class="alignleft" style="margin-right: 2em;">
		<h3><?php printf(__('Group %s', 'leaguemanager'), $group) ?></h3>
		<table class="widefat">
		<thead>
			<tr>
				<th scope="col" class="num">#</th>
				<th scope="col"><?php _e( 'Team', 'leaguemanager' ) ?>
				<th scope="col" class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
			</tr>
		</thead>
		<tbody id="the-list-standings-<?php echo $group ?>">
		<?php if ( $teams ) : $class = ''; ?>
		<?php foreach ( $teams AS $team ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<td class="num"><?php echo $team->rank ?></td>
			<td><?php echo $team->title ?></td>
			<td class="num"><?php printf($league->point_format, $team->points_plus, $team->points_minus) ?></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
	</div>
	
	<?php if ( ($key+1)%4 == 0 ) echo '<br style="clear: both;" />'; ?>
	<?php endforeach; ?>
</div>
