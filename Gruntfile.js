'use strict';
module.exports = function(grunt) {

  // Dynamically loads all required grunt tasks
  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  // Configures the tasks that can be run
  grunt.initConfig({

    // Compiles LESS files to CSS
    less: {
      dist: {
        options: {
          cleancss: true // Minifies CSS output
        },
        files: { 'css/hoverboard_stripe.min.css': 'less/{,*/}*.less' }
      }
    },

    // Adds vendor prefixes to CSS
    autoprefixer: {
      dist: {
        src: 'css/hoverboard_stripe.min.css'
      }
    },

    // Combines and minifies JS files
    uglify: {
      options: {
        mangle: false,
        compress: true,
        preserveComments: 'some'
      },
      scripts: {
        files: {
          'js/hoverboard_stripe.min.js': [
            'js/{,*/}*.js',
            '!js/*.min.js'
          ]
        }
      }
    },

    // Checks JS for syntax issues using JSHint
    jshint: {
        dev: {
          options: {
            jshintrc: true,
            reporter: require('jshint-stylish'),
          },
          src: [ 'js/{,*/}*.js', '!js/*.min.js' ]
        }
    },

    // Looks for todo items and collects them in a file for reference
    todo: {
      options: {
        file: "_TODO.md"
      },
      src: [
        '**/*.js',
        '**/*.less',
        '**/*.php',
        '**/*.mustache',
        '!node_modules/**/*.*',
        '!lib/**/*.*'
      ],
    },

    // Watches front-end files for changes and reruns tasks as needed
    watch: {
      styles: {
        files: [ 'less/{,*/}*.less' ],
        tasks: [ 'less:dist', 'autoprefixer:dist' ],
        options: {
          livereload: true
        }
      },
      scripts: {
        files: [ 'js/{,*/}*.js', '!js/*.min.js' ],
        tasks: [ 'jshint:dev', 'uglify:scripts' ]
      }
    },

  });

  // Compiles LESS/JS and checks for todos
  grunt.registerTask('default', [ 'watch' ]);

};
