import { spawn } from "node:child_process";
import process from "node:process";
import path from "node:path";
import { fileURLToPath } from "node:url";
import readline from "node:readline";
import { createInterface } from "node:readline";
import { PassThrough } from "node:stream";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const phpstanBin = path.resolve(__dirname, "..", "vendor", "bin", "phpstan");

// Preserve default args used by root script:
// vendor/bin/phpstan analyse --memory-limit=512M
const args = ["analyse", "--memory-limit=512M", ...process.argv.slice(2)];

const child = spawn(phpstanBin, args, {
  cwd: path.resolve(__dirname, ".."),
  stdio: ["ignore", "pipe", "pipe"],
  env: process.env,
});

function pipeFiltered(stream, write) {
  const rl = createInterface({
    input: stream.pipe(new PassThrough()),
    crlfDelay: Infinity,
  });

  // PHPStan 1.x prints an "upgrade available" info block. It’s noisy but not actionable in CI.
  // We filter ONLY that block while keeping everything else intact.
  let suppress = false;
  let endOnNextBlank = false;

  rl.on("line", (line) => {
    if (!suppress && line.trim() === "Important: PHPStan 2.x is available.") {
      suppress = true;
      endOnNextBlank = false;
      return;
    }

    if (suppress) {
      // Suppress until after the blog article line, then stop at the next blank line.
      if (line.startsWith("Blog article:")) {
        endOnNextBlank = true;
        return;
      }
      if (endOnNextBlank && line.trim() === "") {
        suppress = false;
        endOnNextBlank = false;
      }
      return;
    }

    write(`${line}\n`);
  });

  rl.on("close", () => {});
}

pipeFiltered(child.stdout, (s) => process.stdout.write(s));
pipeFiltered(child.stderr, (s) => process.stderr.write(s));

child.on("close", (code, signal) => {
  if (signal) {
    process.kill(process.pid, signal);
    return;
  }
  process.exit(code ?? 1);
});
