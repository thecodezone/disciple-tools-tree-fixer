import { defineConfig } from 'vite'
import create_config from '@kucrut/vite-for-wp';
import babel from 'vite-plugin-babel';


export default create_config("src/main.js", 'dist', {
    server: {
        target: 'discipletools.ddev.site',
        https: false
    },
    build: {
        rollupOptions: {
            input: {
                main: 'src/main.js',
            }
        }
    },
    plugins: [
        babel({
            babelConfig: {
                babelrc: false,
                configFile: false,
                assumptions: {
                    "setPublicClassFields": true
                },
                plugins: [
                    [
                        "@babel/plugin-proposal-decorators",
                        {
                            "version": "2018-09",
                            "decoratorsBeforeExport": true
                        }
                    ],
                    [
                        "@babel/plugin-proposal-class-properties"
                    ]
                ]
            },
        })
    ]
})