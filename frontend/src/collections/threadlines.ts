import { Collection } from 'backbone';
import ThreadLineModel from '../models/threadline';

export default class extends Collection {
  public model = ThreadLineModel;
}
