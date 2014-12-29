<div id="pongu_rankings">
	<div class="headers">
		<div class="header">Player</div>
		<div class="header">Rating</div>
		<div class="header">Change</div>
	</div>
	<div class="players">
	</div>
</div>

<div id="match">
	<div class="header">Add Match Result</div>
	<div class="row">
		<span class="cell"><select class ="p1_selectbox"></select></span>
		<span class="cell"><select class ="p2_selectbox"></select></span>
	</div>
	<div class="row">
		<span class="cell"><label for="p1">P1 win</label><input type="radio" name="winner" id="p1"></span>
		<span class="cell"><label for="p2">P2 win</label><input type="radio" name="winner" id="p2"></span>
	</div>
	<button class="add">Add</button>
</div>

<script type="text/javascript">
	var ELO_FIDE_DIFF_TABLE = [392,375,358,345,327,316,303,291,279,268,257,246,236,226,216,207,198,189,180,171,163,154,146,138,130,122,114,107,99,92,84,77,69,62,54,47,40,33,26,18,11,4,0];
	var PLAYER_RANKING_HTML = '\
		<div class="player">\
			<div class="col info">\
				<input type="hidden" name="player_id">\
				<div class="realname">\
					<span class="fname"></span>\
					<span class="lname"></span>\
				</div>\
				<div class="nickname_container">\
					<div class="nickname_img"></div>\
					<div class="nickname"></div>\
				</div>\
			</div>\
			<div class="col realtime_rating"></div>\
			<div class="col change"></div>\
		</div>';
	var rankings;



	function calcRatingChange(r1, r2, score) {
		var k = 40;
		var diff = 0;
		var change = 0;
		var diff = r2 - r1;

		if(Math.abs(diff) > 400) {
			if(r1 > r2) {
				diff = 400;
			}
			else {
				diff = -400;
			}
		}
		change = (score - expected(diff)) * k;
		return Math.round(change);
	}

	function expected(diff) {
		var exp = 92;
		var i = 0;
		while(i<43 && Math.abs(diff) < ELO_FIDE_DIFF_TABLE[i]) {
			i++;
			exp--;
		}
		if(diff > 0)
			return exp / 100.0;
		else
			return 1 - (exp / 100.0);
	}

	function getRankingChanges() {
		var r_by_rating = rankings.slice();
		var r_by_realtime_rating = rankings.slice();
		var rating_delta = 0;
		var rank_delta = 0;
		r_by_rating.sort(sortByRatingDescending);
		r_by_realtime_rating.sort(sortByRealtimeRatingDescending);

		var changes = {};
		$.each(r_by_rating, function(idx, player) {
			changes[player['id']] = {
				'rating_delta':  parseInt(player['realtime_rating']) - parseInt(player['rating'])
			};
			if(changes[player['id']]['rating_delta'] >= 0) {
				changes[player['id']]['rating_delta'] = '+' + changes[player['id']]['rating_delta'];
			}

			$.each(r_by_realtime_rating, function(idx2, playa) {
				if(player['id'] == playa['id']) {
					changes[player['id']]['rank_delta'] = parseInt(idx) - parseInt(idx2);
				}
			});
		});
		// {'rating_delta': rating_delta, 'rank_delta': rank_delta}
		return changes;
	}

	function sortByName(a, b) {
		if(a == b) {
			return 0;
		}
		else if(a < b) {
			return -1;
		}
		else {
			return 1;
		}
	}

	function sortByRatingDescending(a, b) {
		result = parseInt(a['rating']) - parseInt(b['rating']);
		if(result == 0) {
			return sortByName(a['fname'] + ' ' + a['lname'], b['fname'] + ' ' + b['lname']);
		}
		else {
			return result;
		}
	}

	function sortByRealtimeRatingDescending(a, b) {
		result = parseInt(b['realtime_rating']) - parseInt(a['realtime_rating']);
		if(result == 0) {
			return sortByName(a['fname'] + ' ' + a['lname'], b['fname'] + ' ' + b['lname']);
		}
		else {
			return result;
		}
	}

	function refreshRankings() {
		var rankings_container = $('#pongu_rankings .players');
		rankings_container.html('');
		rankings.sort(sortByRealtimeRatingDescending);

		var changes = getRankingChanges();
		var player_div;
		var rank_delta_color;
		var rating_delta_color;
		$.each(rankings, function(idx, player) {
			player_names[player['id']] = '[' +  player['fname'] + ' ' + player['lname'] + '] ' + player['nickname'];
			player_div = $(PLAYER_RANKING_HTML);
			player_div.find('[name="player_id"]').prop('value', player['id']);
			player_div.find('.nickname').html(player['nickname']);
			if(player['nickname'] != '') {
				player_div.find('.nickname_img').css('background', 'url(/assets/img/player_emblems/[32]' + encodeURIComponent(player['nickname']) + '.png)');
			}
			player_div.find('.fname').html(player['fname']);
			player_div.find('.lname').html(player['lname']);
			// player_div.find('.rating').html(player['rating']);
			player_div.find('.realtime_rating').html(player['realtime_rating']);

			if(changes[player['id']]['rank_delta'] < 0) {
				rank_delta_class = 'red';
				changes[player['id']]['rank_delta'] = '&#x25bc;' + Math.abs(parseInt(changes[player['id']]['rank_delta']));
			}
			else {
				rank_delta_class = 'green';
				changes[player['id']]['rank_delta'] = '&#x25b2;' + Math.abs(parseInt(changes[player['id']]['rank_delta']));
			}
			if(changes[player['id']]['rating_delta'] < 0) {
				rating_delta_class = 'red';
			}
			else {
				rating_delta_class = 'green';
			}
			player_div.find('.change').html('<span class="' + rank_delta_class + '">' + changes[player['id']]['rank_delta'] + '</span>&nbsp;(<span class="' + rating_delta_class + '">' + changes[player['id']]['rating_delta'] + '</span>)');

			rankings_container.append(player_div);
		});
	}

	function populateSelectBox(select_element, options, beginning, end) {
		if(typeof beginning === 'undefined') {
			beginning = '';
		}
		if(typeof end === 'undefined') {
			end = '';
		}
		sites_select = beginning;
		$.each(options, function(key, value) {
			sites_select += '<option value="' + key + '">' + value + '</option>';
		});
		sites_select += end;
		select_element.html(sites_select);
	}

	// modified version of:
	// http://stackoverflow.com/questions/12073270/sorting-options-elements-alphabetically-using-jquery
	function sortSelectBoxAlphabetically(select_element, ignore_case) {
		if(typeof ignore_case === 'undefined') {
			ignore_case = true;
		}
		var options = select_element.find('option');
		var arr = options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
		arr.sort(function(o1, o2) {
			if(ignore_case) {
				var t1 = o1.t.toLowerCase();
				var t2 = o2.t.toLowerCase();
			}
			else {
				var t1 = o1.t;
				var t2 = o2.t;
			}
			return t1 > t2 ? 1 : t1 < t2 ? -1 : 0;
		});
		options.each(function(i, o) {
			o.value = arr[i].v;
			$(o).text(arr[i].t);
		});
	}


	$(document).ready(function() {
		$.post('/rankings/getRankings', function(response) {
			rankings = JSON.parse(response);
			// rankings = {};
			// $.each(JSON.parse(response), function(idx, player) {
				// rankings[player['id']] = player;
			// });

			player_names = {};
			refreshRankings();

			populateSelectBox($('#match .p1_selectbox'), player_names);
			// sortSelectBoxAlphabetically($('#match .p1_selectbox'));
			populateSelectBox($('#match .p2_selectbox'), player_names);
			// sortSelectBoxAlphabetically($('#match .p2_selectbox'));
		});



		$(document).delegate('#match .add', 'click', function(event) {
			event.preventDefault();
			event.stopPropagation();

			var match_window = $('#match');
			var winner_checkbox_id = match_window.find('[name="winner"]:checked').attr('id');
			var loser_checkbox_id = match_window.find('[name="winner"]:not(:checked)').attr('id');
			if(typeof winner_checkbox_id === 'undefined') {
				return false;
			}

			var winner_player_id = $('#match .' + winner_checkbox_id + '_selectbox').val();
			var loser_player_id = $('#match .' + loser_checkbox_id + '_selectbox').val();
			if(winner_player_id == loser_player_id) {
				return false;
			}

			var winner;
			var loser;
			$.each(rankings, function(idx, player) {
				if(player['id'] == winner_player_id) {
					winner = player;
				}
				else if(player['id'] == loser_player_id) {
					loser = player;
				}
			});

			score_change = calcRatingChange(winner['realtime_rating'], loser['realtime_rating'], 1)
			winner['realtime_rating'] = parseInt(winner['realtime_rating']) + parseInt(score_change);
			loser['realtime_rating'] = parseInt(loser['realtime_rating']) - parseInt(score_change);
			refreshRankings();
		});
	});
</script>