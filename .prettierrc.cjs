/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

module.exports = {
    ...require("gts/.prettierrc.json"),
    plugins: [require.resolve("@trivago/prettier-plugin-sort-imports")],
    tabWidth: 4,
    useTabs: false,
    printWidth: 120,
    importOrderSeparation: true,
    singleQuote: false,
    semi: true,
};