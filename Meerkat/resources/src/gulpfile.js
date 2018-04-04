var elixir = require('laravel-elixir');

elixir(function(mix) {
	mix.sass('./sass/meerkat.scss', '../assets/css/meerkat.css');

	mix.browserify('./js/templates/dossier.js', 'dist/js/templates/dossier.js');
	mix.browserify('./js/templates/dossier_cell.js', 'dist/js/templates/dossier_cell.js');
	mix.browserify('./js/templates/add_action.js', 'dist/js/templates/add_action.js');
	mix.browserify('./js/templates/stream_cell.js', 'dist/js/templates/stream_cell.js');
	mix.browserify('./js/templates/bulkactions.js', 'dist/js/templates/bulkactions.js');

	mix.scripts([
		'./js/meerkat.js',
		'./js/urls.js',
		'./js/api.js',
		'./js/publisher.js',
		'./js/app.js',

		'./dist/js/templates/dossier.js',
		'./dist/js/templates/dossier_cell.js',
		'./dist/js/templates/add_action.js',
		'./dist/js/templates/stream_cell.js',
		'./dist/js/templates/bulkactions.js',
		'./js/filters/str_limit.js',
		'./js/components/DossierTable.js',
		'./js/components/MeerkatStreamListing.js',
		'./js/components/ConversationView.js'
	], '../assets/js/meerkat.js');

	mix.scripts([
		'./js/dashboard/Chart.bundle.js',
		'./js/dashboard/dashboard.js'
	], '../assets/js/dashboard.js');

	mix.scripts([
		'./js/control-panel/control-panel.js'
	], '../assets/js/control-panel.js');

	mix.scripts([
		'./js/public/replies.js'
	], '../assets/js/reply-to.js');
});