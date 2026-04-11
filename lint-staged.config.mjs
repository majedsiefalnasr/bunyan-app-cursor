/**
 * Staged-file checks for pre-commit. Paths are relative to the repo root.
 * Backend PHP: Laravel Pint (fix staged files), then PHPStan on staged files only.
 *
 * Frontend: ESLint --fix runs only on code extensions, then Prettier --write on every
 * staged prettier target. Prettier must be last so the tree matches `npm run format`
 * (ESLint fixes can otherwise leave Prettier-drift that a full format run would fix).
 *
 * @param {string[]} filenames
 */
function toFrontendPaths(filenames) {
    return filenames.map((f) => f.replace(/^frontend\//, ''));
}

/** Paths under frontend/ that ESLint should fix (json/css are Prettier-only here). */
function isFrontendEslintTarget(repoPath) {
    return /^frontend\/.*\.(vue|ts|js|mjs|cjs)$/i.test(repoPath);
}

function toBackendPaths(filenames) {
    return filenames.map((f) => f.replace(/^backend\//, ''));
}

function shellQuote(paths) {
    return paths.map((p) => JSON.stringify(p)).join(' ');
}

/**
 * Re-stage paths from the repo root after formatters run under frontend/ or backend/.
 * Without this, fixes from `cd frontend && …` / `cd backend && …` often stay unstaged,
 * forcing a second commit for formatting-only changes.
 */
function gitAddStagedPaths(filenames) {
    if (filenames.length === 0) {
        return [];
    }
    return `git add -- ${shellQuote(filenames)}`;
}

export default {
    'frontend/**/*.{vue,ts,js,mjs,cjs,json,css}': (filenames) => {
        if (filenames.length === 0) {
            return [];
        }
        const relAll = toFrontendPaths(filenames);
        const quotedAll = shellQuote(relAll);
        const eslintFilenames = filenames.filter(isFrontendEslintTarget);
        const relEslint = toFrontendPaths(eslintFilenames);
        const cmds = [];
        if (relEslint.length > 0) {
            cmds.push(`cd frontend && npx eslint --max-warnings=0 --fix ${shellQuote(relEslint)}`);
        }
        cmds.push(`cd frontend && npx prettier --write ${quotedAll}`);
        cmds.push(gitAddStagedPaths(filenames));
        return cmds;
    },
    'backend/**/*.php': (filenames) => {
        if (filenames.length === 0) {
            return [];
        }
        const rel = toBackendPaths(filenames);
        const quoted = shellQuote(rel);
        return [
            `cd backend && vendor/bin/pint ${quoted}`,
            `cd backend && vendor/bin/phpstan analyse --memory-limit=512M ${quoted}`,
            gitAddStagedPaths(filenames),
        ];
    },
};
