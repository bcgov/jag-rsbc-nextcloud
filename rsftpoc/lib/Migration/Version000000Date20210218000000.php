<?php

declare(strict_types=1);

namespace OCA\NotesTutorial\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20210218000000 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('rsbcpoc')) {
			$table = $schema->createTable('rsbcpoc');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('title', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('content', 'text', [
				'notnull' => true,
				'default' => ''
			]);
			$table->addColumn('to', 'text', [
				'notnull' => true,
				'default' => ''
			]);
			$table->addColumn('formno', 'text', [
				'notnull' => true,
				'default' => ''
			]);
			$table->addColumn('policeno', 'text', [
				'notnull' => true,
				'default' => ''
			]);
			$table->addColumn('policeemail', 'text', [
				'notnull' => true,
				'default' => ''
			]);
			$table->addColumn('packagetype', 'text', [
				'notnull' => true,
				'default' => ''
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'rsbcpoc_user_id_index');
		}
		return $schema;
	}
}
