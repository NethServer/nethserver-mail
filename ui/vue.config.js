// vue.config.js
module.exports = {
    publicPath: './',
    chainWebpack: config => {
        // 1. ignore manifest-dev.json file
        // 2. override manifest.json during development
        var patterns = []
        if (process.env.NODE_ENV == 'development') {
            patterns.push({
                from: 'public/manifest-dev.json',
                to: 'manifest.json'
            })
        }
        config.plugin('copy').init((Plugin, args) => {
            args[0][0].ignore.push('manifest-dev.json')
            patterns.push.apply(args[0], patterns)
            return new Plugin(...args)
        })
    }
}
