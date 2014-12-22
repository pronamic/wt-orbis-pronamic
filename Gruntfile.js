module.exports = function( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		// Package
		pkg: grunt.file.readJSON( 'package.json' ),
		
		// PHPLint
		phplint: {
			options: {
				phpArgs: {
					'-lf': null
				}
			},
			all: [ '**/*.php', '!node_modules/**', '!bower_components/**' ]
		},
		
		// MakePOT
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages',
					type: 'wp-theme',
					exclude: [ 'bower_components/.*', 'node_modules/.*' ],
				}
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-phplint' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	// Default task(s).
	grunt.registerTask( 'default', [ 'phplint' ] );
	grunt.registerTask( 'pot', [ 'makepot' ] );
};
