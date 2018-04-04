module.exports = (ctx) => ({
    parser: ctx.file.extname === '.sss' ? 'sugarss' : false,
    map: ctx.options.map,
    plugins: [
        require('postcss-import')(),
        require('postcss-nested')(),
        require('autoprefixer')({browsers: 'last 2 versions'}),
        require('cssnano')({preset: 'default'})
    ]
});