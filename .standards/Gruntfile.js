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
        '../assets/scss/variables/*.scss'
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
          '../assets/css/wordpress-news-kios-plugin.css' : '../assets/scss/wordpress-news-kiosk-plugin.scss'
       }
      },
    },
  });

  // These plugins provide necessary tasks
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-phpunit');
  grunt.loadNpmTasks('grunt-scss-lint');
  grunt.loadNpmTasks('grunt-contrib-sass');

  // Styles
  grunt.registerTask('styles', [
    'scsslint',
    'sass:dist',
  ]);

  // Default task
  grunt.registerTask('default', [
    'scsslint',
    'sass:dist',
    'phpcs',
    'phpunit'
  ]);
};

