/**
 * Staged-file checks for pre-commit. Paths are normalized to repo-relative POSIX paths.
 *
 * 1) Root Prettier — same extensions as root package.json "format" (js, mjs, cjs, ts, vue,
 *    css, scss, json, md across the repo), on all staged files except under frontend/.
 *    Under frontend/, the frontend toolchain (Prettier 3 + ESLint) runs instead.
 * 2) Frontend — ESLint --fix on code-like extensions, then Prettier --write (must be last).
 * 3) Backend — Pint + PHPStan on staged PHP files.
 *
 * @param {string[]} filenames
 */
import path from "node:path";
import process from "node:process";

/** Keep in sync with root `package.json` → `format` / `format:check` glob extensions. */
const ROOT_FORMAT_EXT = /\.(js|mjs|cjs|ts|vue|css|scss|json|md)$/i;

/** Paths under .gitignore that `git add` will reject (e.g. tracked legacy paths). */
function isGitignoredPath(relPath) {
  const p = relPath.replace(/\\/g, "/");
  return p === ".cursor" || p.startsWith(".cursor/");
}

function normalizeStagedPaths(filenames) {
  const cwd = process.cwd();
  return filenames.map((f) => {
    const resolved = path.isAbsolute(f) ? f : path.join(cwd, f);
    const rel = path.relative(cwd, path.resolve(resolved));
    return rel.split(path.sep).join("/");
  });
}

function toFrontendPaths(filenames) {
  return filenames.map((f) => f.replace(/^frontend\//, ""));
}

function toBackendPaths(filenames) {
  return filenames.map((f) => f.replace(/^backend\//, ""));
}

/** Paths under frontend/ that ESLint should fix (json/css are Prettier-only here). */
function isFrontendEslintTarget(repoPath) {
  return /^frontend\/.*\.(vue|ts|js|mjs|cjs|md|mdc)$/i.test(repoPath);
}

function shellQuote(paths) {
  return paths.map((p) => JSON.stringify(p)).join(" ");
}

function gitAddStagedPaths(filenames) {
  if (filenames.length === 0) {
    return [];
  }
  return `git add -- ${shellQuote(filenames)}`;
}

/**
 * @param {string[]} allStagedFiles
 * @returns {string | string[]}
 */
export default function lintStaged(allStagedFiles) {
  const files = normalizeStagedPaths(allStagedFiles);
  /** @type {string[]} */
  const commands = [];

  // --- Root-style Prettier (mirrors root package.json "format"), excluding frontend/ ---
  const rootPrettierTargets = files.filter(
    (f) =>
      !f.startsWith("frontend/") &&
      !isGitignoredPath(f) &&
      ROOT_FORMAT_EXT.test(f)
  );
  if (rootPrettierTargets.length > 0) {
    commands.push(`npx prettier --write ${shellQuote(rootPrettierTargets)}`);
    commands.push(gitAddStagedPaths(rootPrettierTargets));
  }

  // --- Frontend (Prettier + ESLint from frontend/) ---
  const frontendPattern =
    /^frontend\/.*\.(vue|ts|js|mjs|cjs|json|css|md|mdc)$/i;
  const frontendFiles = files.filter((f) => frontendPattern.test(f));
  if (frontendFiles.length > 0) {
    const relAll = toFrontendPaths(frontendFiles);
    const eslintFilenames = frontendFiles.filter(isFrontendEslintTarget);
    const relEslint = toFrontendPaths(eslintFilenames);
    if (relEslint.length > 0) {
      commands.push(
        `cd frontend && npx eslint --max-warnings=0 --fix ${shellQuote(
          relEslint
        )}`
      );
    }
    commands.push(`cd frontend && npx prettier --write ${shellQuote(relAll)}`);
    commands.push(gitAddStagedPaths(frontendFiles));
  }

  // --- Backend PHP ---
  const backendPhp = files.filter((f) => /^backend\/.*\.php$/i.test(f));
  if (backendPhp.length > 0) {
    const rel = toBackendPaths(backendPhp);
    const quoted = shellQuote(rel);
    commands.push(`cd backend && vendor/bin/pint ${quoted}`);
    commands.push(
      `cd backend && vendor/bin/phpstan analyse --memory-limit=512M ${quoted}`
    );
    commands.push(gitAddStagedPaths(backendPhp));
  }

  return commands;
}
