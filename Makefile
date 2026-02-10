.PHONY: docs docs-fix docs-index docs-paths docs-schema hooks-install refactor-audit p0-baseline

docs:
	@bash scripts/check-docs.sh

docs-fix:
	@bash scripts/generate-containers-index.sh docs/containers-index.md auto-generated
	@echo "Regenerated docs/containers-index.md"

docs-index:
	@bash scripts/generate-containers-index.sh docs/containers-index.md auto-generated

docs-paths:
	@bash scripts/check-doc-paths.sh

docs-schema:
	@bash scripts/check-container-readmes-schema.sh

hooks-install:
	@bash scripts/install-git-hooks.sh

refactor-audit:
	@bash scripts/audit-refactor-checklist.sh

p0-baseline:
	@bash scripts/run-p0-baseline-tests.sh
