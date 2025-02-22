module.exports = {
    ...require('gts/.prettierrc.json'),
    plugins: [require.resolve('@trivago/prettier-plugin-sort-imports')],
    endOfLine: 'auto',
    tabWidth: 4,
    useTabs: false,
    printWidth: 120,
    importOrderSeparation: true,
};