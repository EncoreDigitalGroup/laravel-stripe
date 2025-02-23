/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

const esbuild = require("esbuild");

esbuild.build({
    entryPoints: ["src/ts/index.ts"],
    bundle: true,
    // minify: true,
    outfile: "dist/bundle.js",
    platform: "browser",
    target: ["es2020"],
    format: "iife",
}).catch(() => process.exit(1));