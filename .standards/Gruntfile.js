/**
 *
 * @see  https://github.com/squizlabs/PHP_CodeSniffer
 */
 module.exports = function (grunt) {
  'use strict';
  // Project configuration
  grunt.initConfig({
    // Metadata
    pkg: grunt.file.readJSON('package.json'),
    banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
    '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
    '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
    '* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
    ' Licensed <%= props.license %> */\n',
    // PHP Coding Standards
    // ====================
    phpcs: {
      core: {
        dir: ['../*.php']
      },
      admin: {
        dir: [
        '../admin/*.php',
        '../admin/*/*.php',
        ]
      },
      includes: {
        dir: [
        '../includes/*.php',
        ]
      },
      shortcodes: {
        dir: [
        '../shortcodes/*.php',
        ]
      },
      globals: {
        dir: [
        '../globals/*.php',
        ]
      },
      helpers: {
        dir: [
        '../helpers/*.php',
        ]
      },

      pages: {
        dir: [
        '../pages/*.php',
        '../pages/*/*.php',
        ]
      },

      plugins: {
        dir: [
        '../plugin/*.php',
        ]
      },

      page_templates: {
        dir: [
        '../page-templates/*.php',
        '../page-templates/*/*.php'
        ]
      },

      // leaving this commented out because the 'camel caps format' rule doesn't apply here
      tests: {
        dir: [
        '../tests/*.php',
        ]
      },
      options: {
        // bin: './vendor/bin/phpcbf', // for Fixing coding standards
        bin: './vendor/bin/phpcs',
        standard: [
          'WordPress-Core'
        ]
      }
    },

    // PHPUnit
    // =======
    phpunit: {
      configuration: 'phpunit.xml',
      options: {
        bin: './vendor/bin/phpunit',
        color: true
      }
    },
    // SCSS Lint
    // =========
    scsslint: {
      allFiles: [
        '../assets/scss/*.scss',
        '../assets/scss/components/*.scss',
        '../assets/scss/page-components/*.scss',
        '../assets/scss/variables/*.scss',
        '../assets/scss/mixins/*.scss'
      ],
      options: {
        config: '../assets/scss/.scss-lint.yml'
      }
    },
    // SASS Compile
    // ============
    sass: {
      options: {
        style: 'expanded',
        sourcemap: 'auto'
      },
      dist: {
        files: {
          '../assets/css/wordpress-news-kiosk-plugin.css' : '../assets/scss/wordpress-news-kiosk-plugin.scss'
       }
      },
    },
    watch: {
      styles: {
        files: ['<%= scsslint.allFiles %>'],
        tasks: ['sass:dist'],
        options: {
          spawn: false,
        },
      },
    },
    // JS Hint
    // =======
    jshint: {
      options: {
        jshintrc: '../assets/js/coding_standards/.jshintrc'
      },
      core: {
        src: [
          '../assets/js/src/*.js',
        ]
      }
    },
    // JS Coding Style
    // ===============
    jscs: {
      options: {
        config: '../assets/js/coding_standards/.jscsrc'
      },
      core: {
        src: '<%= jshint.core.src %>'
      }
    },
    // JS Compile
    // ==========
    concat: {
      core: {
        src: [
          '../assets/js/src/*.js'
        ],
        dest: '../assets/js/build/wordpress-news-kiosk-plugin.js'
      }
    },
    // JS Uglify
    // =========
    uglify: {
      options: {
        preserveComments: 'some'
      },
      core: {
        src: '<%= concat.core.dest %>',
        dest: '../assets/js/build/wordpress-news-kiosk-plugin.min.js'
      }
    },
  });

  // These plugins provide necessary tasks
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-phpunit');
  grunt.loadNpmTasks('grunt-scss-lint');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-jscs');

  // Watch Styles
  grunt.registerTask('watchStyles', [
    'watch:styles'
  ]);

  // Styles
  grunt.registerTask('styles', [
    'scsslint',
    'sass:dist',
  ]);

  // JSlint
  grunt.registerTask('jslint', [
    'jshint',
    'jscs',
    'concat',
    'uglify',
  ]);

  // Default task
  grunt.registerTask('default', [
    'scsslint',
    'sass:dist',
    'phpcs',
    'jshint',
    'jscs',
    'concat',
    'uglify',
    'phpunit'
  ]);
};
