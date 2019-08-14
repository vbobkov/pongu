<div style="display:none"><button id="autoscroll">Auto Scroll Rankings</button></div>
<div style="display:none"><button id="autocycle">Auto Cycle Stats</button></div>
<div id="pongu_rankings">
	<div class="headers">
		<div class="header">Player</div>
		<div class="header">Rating</div>
		<div class="header">Change</div>
		<div class="header">All Time High</div>
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
	<button class="undo">Undo Last Match</button>
	<div class="header history-header">Combat Log</div>
	<div class="history">
	</div>
	<div class="rank-changes">Showing rank/rating changes since <span class="rank-changes-timestamp"></span>.</div>
	<button class="reset-rank-epoch">Reset rank changes</button>
</div>

<div id="player_stats">
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
			<div class="col all-time-high"></div>\
		</div>';
	var PLAYER_STATS_INTRO_HTML = '\
		<div class="intro">\
			<div class="emblem"></div>\
			<div class="names">\
				<div class="realname">\
					<span class="fname"></span>\
					<span class="lname"></span>\
				</div>\
				<div class="nickname"></div>\
			</div>\
		</div>';
	var PLAYER_WIN_LOSS_HTML = '\
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
			<div class="col wins"></div>\
			<div class="col losses"></div>\
		</div>';
	var HISTORY_LINE_HTML = '<div class="line"><span class="timestamp"></span><span class="caster"></span><span class="spell"></span><span class="target"></span></div>';
	var T3H_DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
	var T3H_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
	var T3H_FINISHERS = {
		0: 'Slam of Explodification',
		1: 'Topspin of Destruction',
		2: 'Backspin of Evil',
		3: 'Serve of Annihilation',
		4: 'Slice of Dismemberment',
		5: 'Block of Bludgeoning',
		6: 'Corner Shot of Death',
		7: 'Edge Shot of Pulverization',
		8: 'Explodifying Slam',
		9: 'Terrifying Topspin',
		10: 'Diabolical Backspin',
		11: 'Net Shot of Devastation',
		12: 'Wicked Slice',
		13: 'Sinister Sidespin',
		14: 'Sidespin of Eradication'
	};
	var T3H_PLAYER_FINISHERS = {
		1: [0,1,3,8,11],
		2: [0,1,2,4,9,10,11,12,13,14],
		3: [0,1,6,7,8,9,11],
		4: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		5: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		6: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		7: [0,1,8],
		8: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		9: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		10: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		11: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		12: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		13: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		14: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		15: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		16: [0,1,2,4,9,10,11,12,13,14],
		17: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		18: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		19: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		20: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		21: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		22: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		23: [0,1,8,11],
		24: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		25: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		26: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		27: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		28: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		29: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		30: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		31: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14],
		32: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]
	};
	var HISTORY_LIMIT = 18;
	var REDIS_LAST_SYNCED = Date.now() / 1000;
	var REDIS_PINGER;
	var battle_history = [];
	var battle_results = [];
	var combat_log = [];
	var rankings;
	var rank_epoch;
	var update_rankings = false;

	var SCROLL_DIRECTION = 1;
	var RANKINGS_SCROLLER;

	var STATS_CURRENT_CYCLE_TARGET = null;
	var STATS_CYCLER;



	function calcRatingChange(r1, r2, score) {
		var k = 40;
		var change = 0;
		var diff = r1 - r2;

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
		return changes;
	}



	function autoScrollRankings(increment, delay) {
		var page = $('body');
		if(SCROLL_DIRECTION == 1 && page.scrollTop() < $(document).height() - $(window).height() && page.scrollTop() + increment > $(document).height() - $(window).height()) {
			SCROLL_DIRECTION = -1;
		}
		else if(SCROLL_DIRECTION == -1 && page.scrollTop() > 0 && page.scrollTop() - increment < 0) {
			SCROLL_DIRECTION = 1;
		}
		page.animate({scrollTop: page.scrollTop() + (SCROLL_DIRECTION * increment)}, delay);
	}

	function checkRedisForNewRankings() {
		$.post('/rankings/getRankings', function(response) {
			response = JSON.parse(response);
			rankings = response['rankings'];
			rank_epoch = response['rank_epoch'][0];
			response = null;

			player_names = {};
			refreshRankings();
			refreshRankEpoch();
			refreshCombatLog();
		});


		/*
		$.post('/red/getMatchUpdates', {'redis_last_synced': REDIS_LAST_SYNCED}, function(response) {
			if(response != null && response != '') {
				var redis_response = JSON.parse(response);
				if(redis_response.length > 0) {
					REDIS_LAST_SYNCED = redis_response[0];
				}
				if(redis_response.length > 1 && redis_response[1].length > 0) {
					rankings = JSON.parse(redis_response[1][0][0]);
					// combat_log = JSON.parse(redis_response[2][0][0]);
					// if(typeof combat_log !== 'object') {
					// 	combat_log = [];
					// }
				}
			}
			if(update_rankings) {
				// update_rankings = false;
				saveRankings();
				$.post('/rankings/saveBattles', {'battle_history': battle_history, 'battle_results': battle_results}, function(response) {
				});
				$.post('/rankings/saveCombatLog', {'combat_log': combat_log}, function(response) {
					update_rankings = false;
					refreshCombatLog();
				});
			}
			else {
				refreshRankings();
				$.post('/rankings/getCombatLog', function(response2) {
					combat_log = JSON.parse(response2);
					refreshCombatLog();
				});
			}
		});
		*/
	}

	function convertDateToYMDHMS(d) {
		return [d.getFullYear(), ('0' + (d.getMonth() + 1)).slice(-2), ('0' + d.getDate()).slice(-2)].join('-') + ' ' + ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2) + ':' + ('0' + d.getSeconds()).slice(-2);
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

	function refreshCombatLog() {
		$.post('/rankings/getCombatLog', {}, function(response) {
			combat_log = JSON.parse(response);
			while(combat_log.length > HISTORY_LIMIT) {
				combat_log.shift();
			}
			var combat_log_container = $('#match .history');
			combat_log_container.html('');
			var new_combat_log_line;
			$.each(combat_log, function(idx, entry) {
				new_combat_log_line = $(HISTORY_LINE_HTML);
				new_combat_log_line.find('.timestamp').html('[' + entry['time'] + ']');
				new_combat_log_line.find('.caster').html(entry['caster']);
				new_combat_log_line.find('.spell').html('{' + entry['spell'] + '}');
				new_combat_log_line.find('.target').html(entry['target']);
				combat_log_container.append(new_combat_log_line);
			});
		});
	}

	function refreshRankEpoch() {
		var rd = rank_epoch['last_sync'].split("-");
		var rank_epoch_date = new Date(rd[0], rd[1] - 1, rd[2].split(' ')[0], rd[2].split(' ')[1].split(':')[0], rd[2].split(' ')[1].split(':')[1], rd[2].split(' ')[1].split(':')[2]);
		rank_epoch_date.setHours(rank_epoch_date.getHours() - Math.round(rank_epoch_date.getTimezoneOffset() / 60));

		$('#match .rank-changes .rank-changes-timestamp').html(T3H_DAYS[rank_epoch_date.getDay()] + ', ' + T3H_MONTHS[rank_epoch_date.getMonth()] + ' ' + rank_epoch_date.getDate() + ', ' + rank_epoch_date.getFullYear());
		// $('#match .rank-changes .rank-changes-timestamp').html(rank_epoch_date.toString());
	}

	function refreshRankings() {
		var rankings_container = $('#pongu_rankings .players');
		rankings_container.html('');
		// rankings.sort(sortByRealtimeRatingDescending);
		rankings.sort(sortByAFKAndRealtimeRatingDescending);

		var changes = getRankingChanges();
		var player_div;
		var rank_delta_color;
		var rating_delta_color;
		$.each(rankings, function(idx, player) {
			player_names[player['id']] = '[' +  player['fname'] + ' ' + player['lname'] + '] ' + player['nickname'];
			player_div = $(PLAYER_RANKING_HTML);
			player_div.find('[name="player_id"]').prop('value', player['id']);

			if(player['afk'] > 0) {
				player_div.find('.nickname').html(player['nickname'] + '&nbsp;<span style="color:#f00">(AFK)</span>');
			}
			else {
				player_div.find('.nickname').html(player['nickname']);
			}

			if(player['nickname'] != '') {
				player_div.find('.nickname_img').css('background', 'url(/assets/img/player_emblems/[32]' + encodeURIComponent(player['nickname']).replace(/'/g, "%27") + '.png)');
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
			player_div.find('.all-time-high').html('<span>' + player['highest_rank'] + '</span>&nbsp;(<span>' + player['highest_rating'] + '</span>)');

			rankings_container.append(player_div);
		});
	}

	function saveRankings() {
		$.each(rankings, function(idx, player) {
			if(player['highest_rank'] == null || player['highest_rank'] <= 0 || player['highest_rank'] > idx + 1) {
				player['highest_rank'] = idx + 1;
			}
			if(player['highest_rating'] == null || player['highest_rating'] <= 0 || player['highest_rating'] < player['realtime_rating']) {
				player['highest_rating'] = player['realtime_rating'];
			}
		});
		$.post('/rankings/saveRankings', {'rankings': rankings, 'rank_epoch': rank_epoch}, function(response) {
			// $.post('/red/saveMatchUpdates', {'rankings': rankings}, function(response2) {
			// });
			refreshRankings();
		});
	}

	function sortByName(a, b) {
		var name1 = a['fname'] + ' ' + a['lname'];
		var name2 = b['fname'] + ' ' + b['lname'];
		if(name1 == name2) {
			return 0;
		}
		else if(name1 < name2) {
			return -1;
		}
		else {
			return 1;
		}
	}

	function sortByRatingDescending(a, b) {
		var result = parseInt(b['rating']) - parseInt(a['rating']);
		if(result == 0) {
			return sortByName(a, b);
		}
		else {
			return result;
		}
	}

	function sortByRealtimeRatingDescending(a, b) {
		var result = parseInt(b['realtime_rating']) - parseInt(a['realtime_rating']);
		if(result == 0) {
			return sortByName(a, b);
		}
		else {
			return result;
		}
	}

	function sortByAFKAndRealtimeRatingDescending(a, b) {
		var result = (-1 * parseInt(b['afk'])) - (-1 * parseInt(a['afk']));
		if(result == 0) {
			return sortByRealtimeRatingDescending(a, b);
		}
		else {
			return result;
		}
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
			response = JSON.parse(response);
			rankings = response['rankings'];
			rank_epoch = response['rank_epoch'][0];
			response = null;
			// rankings = {};
			// $.each(JSON.parse(response), function(idx, player) {
				// rankings[player['id']] = player;
			// });

			player_names = {};
			refreshRankings();
			refreshRankEpoch();

			checkRedisForNewRankings();
			clearInterval(REDIS_PINGER);
			REDIS_PINGER = setInterval(function() {
				checkRedisForNewRankings();
			}, 5000);

			populateSelectBox($('#match .p1_selectbox'), player_names);
			sortSelectBoxAlphabetically($('#match .p1_selectbox'));
			populateSelectBox($('#match .p2_selectbox'), player_names);
			sortSelectBoxAlphabetically($('#match .p2_selectbox'));
		});



		$(document).delegate('html, body', 'click', function(event) {
			if($(event.target).attr('id') != 'autoscroll') {
				clearInterval(RANKINGS_SCROLLER);
			}
			if($(event.target).attr('id') != 'autocycle' && !$(event.target).hasClass('nickname')) {
				clearInterval(STATS_CYCLER);
			}
		});

		$(document).delegate('#autoscroll', 'click', function(event) {
			event.stopPropagation();
			event.preventDefault();
			$('body').stop();
			$('body').scrollTop(0);
			SCROLL_DIRECTION = 1;
			clearInterval(RANKINGS_SCROLLER);
			RANKINGS_SCROLLER = setInterval(function() {autoScrollRankings(50,2000);}, 2000);
		});

		$(document).delegate('#autocycle', 'click', function(event) {
			event.stopPropagation();
			event.preventDefault();
			STATS_CURRENT_CYCLE_TARGET = 0;
			clearInterval(STATS_CYCLER);
			STATS_CYCLER = setInterval(function() {
				var player_containers = $('#pongu_rankings .players .player');
				var this_player_container = $(player_containers[STATS_CURRENT_CYCLE_TARGET]);
				var this_player_nickname = this_player_container.find('.info .nickname_container .nickname');
				if(this_player_nickname.text().indexOf('(AFK)') != -1) {
					STATS_CURRENT_CYCLE_TARGET = 0;
					this_player_container = $(player_containers[STATS_CURRENT_CYCLE_TARGET]);
					this_player_nickname = this_player_container.find('.info .nickname_container .nickname');
				}
				this_player_nickname.click();
				if(STATS_CURRENT_CYCLE_TARGET < player_containers.length) {
					STATS_CURRENT_CYCLE_TARGET++;
				}
				else {
					STATS_CURRENT_CYCLE_TARGET = 0;
				}
			}, 10000);
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

			battle_history = [];
			battle_results = [];
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
			new_battle_history_entry = {
				'id': -1,
				'winner_id': winner['id'],
				'loser_id': loser['id'],
				'winner_old_rating': winner['realtime_rating'],
				'loser_old_rating': loser['realtime_rating']
			};
			battle_results.push({
				'id': -1,
				'player_id': winner['id'],
				'opponent_id': loser['id'],
				'wins': 1
			});
			var new_combat_log_entry = {
				'id': -1,
				'time': convertDateToYMDHMS(new Date()),
				'caster': winner['nickname'],
				'spell': T3H_FINISHERS[T3H_PLAYER_FINISHERS[winner['id']][Math.floor(Math.random() * T3H_PLAYER_FINISHERS[winner['id']].length)]],
				'target': loser['nickname']
			};

			score_change = calcRatingChange(winner['realtime_rating'], loser['realtime_rating'], 1)
			new_battle_history_entry['rating_change'] = score_change;
			battle_history.push(new_battle_history_entry);
			winner['realtime_rating'] = parseInt(winner['realtime_rating']) + parseInt(score_change);
			loser['realtime_rating'] = parseInt(loser['realtime_rating']) - parseInt(score_change);

			combat_log.push(new_combat_log_entry);

			saveRankings();
			$.post('/rankings/saveCombatLog', {'combat_log': combat_log}, function(response) {
				refreshCombatLog();
			});
			$.post('/rankings/saveBattles', {'battle_history': battle_history, 'battle_results': battle_results}, function(response) {
			});
		});

		$(document).delegate('#match .undo', 'click', function(event) {
			$.post('/rankings/getLastMatch', function(response) {
				var last_change = JSON.parse(response);

				var winner = null;
				var loser = null;
				var winner_idx = -1;
				var loser_idx = -1;
				$.each(rankings, function(idx, player) {
					if(player['id'] == last_change['winner_id']) {
						winner = player;
						winner_idx = idx;
					}
					else if(player['id'] == last_change['loser_id']) {
						loser = player;
						loser_idx = idx;
					}
				});
				if(winner == null || loser == null) {
					return false;
				}

				// winner['realtime_rating'] = parseInt(winner['realtime_rating']) - parseInt(last_change['rating_change']);
				// loser['realtime_rating'] = parseInt(loser['realtime_rating']) + parseInt(last_change['rating_change']);
				rankings[winner_idx]['realtime_rating'] = parseInt(winner['realtime_rating']) - parseInt(last_change['rating_change']);
				rankings[loser_idx]['realtime_rating'] = parseInt(loser['realtime_rating']) + parseInt(last_change['rating_change']);

				saveRankings();
				$.post('/rankings/undoLastMatch', function(response) {
					refreshRankings();
					refreshCombatLog();
				});
			});
		});

		$(document).delegate('#match .reset-rank-epoch', 'click', function(event) {
			rank_epoch['last_sync'] = convertDateToYMDHMS(new Date());
			$.each(rankings, function(idx, player) {
				player['rating'] = player['realtime_rating'];
			});
			refreshRankEpoch();
			saveRankings();
		});

		$(document).delegate('#pongu_rankings .players .player .nickname', 'click', function(event) {
			var player_id = $(this).closest('.player').find('[name="player_id"]').prop('value');
			var t3h_player;
			$.each(rankings, function(idx, playa) {
				if(player_id == playa['id']) {
					t3h_player = playa;
				}
			});
			$.post('/rankings/getBattles', {'player_id': player_id}, function(response) {
				var player_battles = JSON.parse(response);
				var player_stats_window = $('#player_stats')

				player_stats_window.css('display', '');
				player_stats_window.html('');
				player_stats_window.css('display', 'block');

				var player_intro = $(PLAYER_STATS_INTRO_HTML);
				player_intro.find('.emblem').css('background', 'url(/assets/img/player_emblems/[256]' + encodeURIComponent(t3h_player['nickname']).replace(/'/g, "%27") + '.png)');
				player_intro.find('.nickname').html(t3h_player['nickname']);
				player_intro.find('.fname').html(t3h_player['fname']);
				player_intro.find('.lname').html(t3h_player['lname']);

				var wins_and_losses_header = $('\
					<div class="headers">\
						<div class="header">Opponent</div>\
						<div class="header">Wins</div>\
						<div class="header">Losses</div>\
					</div>');
				var wins_and_losses = $('<div class="players"></div>');
				var player_div;
				var wins;
				var losses;

				// sorted_rankings = rankings.slice();
				// sorted_rankings.sort(sortByName);
				// $.each(sorted_rankings, function(idx, player) {
				$.each(rankings, function(idx, player) {
					if(player['id'] != player_id) {
						player_div = $(PLAYER_WIN_LOSS_HTML);
						player_div.find('[name="player_id"]').prop('value', player['id']);
						player_div.find('.nickname').html(player['nickname']);
						if(player['nickname'] != '') {
							player_div.find('.nickname_img').css('background', 'url(/assets/img/player_emblems/[32]' + encodeURIComponent(player['nickname']).replace(/'/g, "%27") + '.png)');
						}
						player_div.find('.fname').html(player['fname']);
						player_div.find('.lname').html(player['lname']);
						wins = 0;
						losses = 0;
						$.each(player_battles, function(idx3, battle) {
							if(battle['opponent_id'] == player['id']) {
								wins = battle['wins'];
							}
							else if(battle['player_id'] == player['id']) {
								losses = battle['wins'];
							}
						});
						player_div.find('.wins').html(wins);
						player_div.find('.losses').html(losses);
						wins_and_losses.append(player_div);
					}
				});
				player_stats_window.append(player_intro);
				player_stats_window.append(wins_and_losses_header);
				player_stats_window.append(wins_and_losses);

				var anim_param = -1200;
				player_stats_window.css('left', anim_param);
				var window_open_animation = setInterval(function() {
					if(parseInt(player_stats_window.css('left')) <= 150) {
						player_stats_window.css('left', anim_param);
						anim_param += 25;
					}
					else {
						player_stats_window.css('left', '');
						clearInterval(window_open_animation);
					}
				}, 5);
			});
		});

		$(document).delegate('html', 'click', function(event) {
			if($(event.target).closest('#player_stats').length < 1) {
				$('#player_stats').css('display', '');
			}
		});
	});
</script>
