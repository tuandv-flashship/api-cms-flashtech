.PHONY: docs docs-fix docs-index docs-paths docs-schema

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
